<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MediaLibraryResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroBanner;
use App\Models\HeroSlide;
use App\Models\HomepageBlock;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\MediaReuseService;
use App\Support\MediaDisk;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Filament\Columns\StoredMediaUrlColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

/**
 * "Media library" — every image the shop stores, in one place.
 *
 * The point of this page (docs/media-reuse.md → M-10) is **reuse without
 * re-uploading**: find an image that is already in the system and attach it
 * anywhere with the "Use elsewhere" action. Nothing is copied — the new usage
 * is a shared media row that resolves to the one stored file, so the same
 * picture used on five products still exists exactly once.
 *
 * Read-only by design: images enter the system through the media fields of the
 * records that own them (docs/media-architecture.md → M-8).
 */
class MediaLibraryResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'CONTENT';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Media library';

    protected static ?string $modelLabel = 'image';

    protected static ?string $pluralModelLabel = 'Media library';

    protected static ?string $slug = 'media-library';

    /** Where an image can be reused from this page: model class ⇒ picker label. */
    private const REUSE_TARGETS = [
        Product::class => 'Product — gallery / social image',
        ProductVariant::class => 'Product variant — images',
        Category::class => 'Category — icon',
        Brand::class => 'Brand — logo',
        HeroSlide::class => 'Hero slide — desktop / mobile image',
        HeroBanner::class => 'Hero banner — image',
        HomepageBlock::class => 'Homepage block — image',
    ];

    /** @var array<string, list<string>> */
    private static array $searchableColumns = [];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows('delete', $record);
    }

    /** The owner row is shown on reused rows — eager load it (no N+1). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('sharedOwner');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // StoredMediaUrlColumn: media URLs are host-relative
                // (`/storage/...`), which Filament's ImageColumn would look up
                // as a disk path and render nothing (see the column's docblock).
                StoredMediaUrlColumn::make('preview')
                    ->label('Image')
                    ->state(fn (Media $record): string => $record->getUrl())
                    ->imageHeight(48),
                TextColumn::make('file_name')
                    ->label('File')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => Str::limit($state, 38))
                    ->tooltip(fn (Media $record): string => $record->file_name)
                    ->description(fn (Media $record): ?string => $record->isShared()
                        ? 'Reuses: '.($record->sharedOwner?->file_name ?? 'the stored file')
                        : null),
                TextColumn::make('kind')
                    ->label('Row')
                    ->state(fn (Media $record): string => $record->isShared() ? 'Reused' : 'Original')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Reused' ? 'warning' : 'gray'),
                TextColumn::make('collection_name')
                    ->label('Place')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('disk')
                    ->badge()
                    ->color(fn (string $state): string => $state === MediaDisk::CLOUDINARY ? 'success' : 'gray'),
                TextColumn::make('human_readable_size')
                    ->label('Size')
                    ->alignEnd(),
                TextColumn::make('used_in')
                    ->label('Used in')
                    // Every place that resolves to the same stored file — the
                    // owner row plus each reuse — not just this row's children.
                    ->state(fn (Media $record): int => app(MediaReuseService::class)->usageCount($record))
                    ->badge()
                    ->alignCenter()
                    ->color(fn (int|string|null $state): string => (int) $state > 1 ? 'info' : 'gray')
                    ->tooltip(fn (Media $record): string => app(MediaReuseService::class)->usageSummary($record)),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('collection_name')
                    ->label('Place')
                    ->options(fn (): array => Media::query()->distinct()->orderBy('collection_name')
                        ->pluck('collection_name', 'collection_name')->all()),
                SelectFilter::make('disk')
                    ->options(fn (): array => Media::query()->distinct()->orderBy('disk')
                        ->pluck('disk', 'disk')->all()),
                SelectFilter::make('row_kind')
                    ->label('Row type')
                    ->options(['original' => 'Original uploads only', 'reused' => 'Reused rows only'])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'original' => $query->whereNull('shared_path'),
                            'reused' => $query->whereNotNull('shared_path'),
                            default => $query,
                        };
                    }),
                Filter::make('duplicates')
                    ->label('Same file stored more than once')
                    ->toggle()
                    ->visible(fn (): bool => SchemaFacade::hasColumn('media', 'checksum'))
                    ->query(fn (Builder $query, array $data): Builder => ($data['isActive'] ?? false)
                        ? $query->whereIn('checksum', Media::query()
                            ->fileOwners()
                            ->whereNotNull('checksum')
                            ->where('checksum', '!=', '')
                            ->select('checksum')
                            ->groupBy('checksum', 'disk')
                            ->havingRaw('COUNT(*) > 1'))
                        : $query),
            ])
            ->actions([
                Action::make('open')
                    ->label('Open file')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Media $record): string => $record->getUrl())
                    ->openUrlInNewTab(),
                Action::make('reuse')
                    ->label('Use elsewhere')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->visible(fn (): bool => Gate::allows('create', Media::class))
                    ->modalHeading('Use this image elsewhere')
                    ->modalDescription(fn (Media $record): string => 'Used in: '.app(MediaReuseService::class)->usageSummary($record))
                    ->modalSubmitActionLabel('Use this image')
                    ->schema([
                        Select::make('target_type')
                            ->label('Use in')
                            ->options(self::REUSE_TARGETS)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('record_id', null)),
                        Select::make('record_id')
                            ->label('Item')
                            ->required()
                            ->searchable()
                            ->options(fn (Get $get): array => self::recordOptions($get('target_type')))
                            ->getSearchResultsUsing(fn (Get $get, string $search): array => self::recordOptions($get('target_type'), $search)),
                        Select::make('collection')
                            ->label('Place')
                            ->required()
                            ->options(fn (Get $get): array => self::collectionOptions($get('target_type'))),
                    ])
                    ->action(function (Media $record, array $data): void {
                        $modelClass = self::targetModel($data['target_type'] ?? null);
                        $target = $modelClass !== null
                            ? $modelClass::query()->find($data['record_id'] ?? null)
                            : null;
                        $collection = trim((string) ($data['collection'] ?? ''));

                        if (! $target instanceof HasMedia
                            || $collection === ''
                            || $target->getMediaCollection($collection) === null) {
                            Notification::make()->title('Choose an item and a place for this image')->danger()->send();

                            return;
                        }

                        app(MediaReuseService::class)->attach($record, $target, $collection);

                        Notification::make()
                            ->title('Image reused — no new copy stored')
                            ->body('Added to '.self::recordLabel($target).' ('.$collection.').')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->modalDescription(fn (Media $record): string => 'Used in: '.app(MediaReuseService::class)->usageSummary($record)
                        .'. If the image is still used elsewhere, only this row is removed and the stored file stays.'),
            ])
            ->emptyStateHeading('No images yet')
            ->emptyStateDescription('Upload images from a product, variant, category, brand, hero slide or homepage block — they all show up here and can be reused anywhere.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMediaLibrary::route('/'),
        ];
    }

    /**
     * The model class behind a picker value — only the whitelisted targets, so
     * a tampered request can never instantiate an arbitrary class.
     *
     * @return class-string<Model>|null
     */
    private static function targetModel(mixed $type): ?string
    {
        return is_string($type) && array_key_exists($type, self::REUSE_TARGETS) ? $type : null;
    }

    /**
     * @return array<int|string, string>
     */
    private static function recordOptions(mixed $type, string $search = ''): array
    {
        $modelClass = self::targetModel($type);

        if ($modelClass === null) {
            return [];
        }

        $query = $modelClass::query();

        $search = trim($search);

        if ($search !== '') {
            $columns = self::searchableColumns($modelClass);
            $like = '%'.$search.'%';

            $query->where(function (Builder $inner) use ($columns, $like): void {
                foreach ($columns as $column) {
                    $inner->orWhere($column, 'like', $like);
                }
            });
        }

        return $query
            ->when(method_exists($modelClass, 'product'), fn (Builder $builder) => $builder->with('product'))
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Model $record): array => [$record->getKey() => self::recordLabel($record)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function collectionOptions(mixed $type): array
    {
        $modelClass = self::targetModel($type);
        $instance = $modelClass !== null ? new $modelClass : null;

        return $instance instanceof HasMedia ? app(MediaReuseService::class)->availableCollections($instance) : [];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return list<string>
     */
    private static function searchableColumns(string $modelClass): array
    {
        if (isset(self::$searchableColumns[$modelClass])) {
            return self::$searchableColumns[$modelClass];
        }

        $table = (new $modelClass)->getTable();

        return self::$searchableColumns[$modelClass] = array_values(array_filter(
            ['name', 'title', 'slug'],
            fn (string $column): bool => SchemaFacade::hasColumn($table, $column),
        ));
    }

    private static function recordLabel(Model $record): string
    {
        foreach (['name', 'title', 'slug'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return class_basename($record).' #'.$record->getKey();
    }
}
