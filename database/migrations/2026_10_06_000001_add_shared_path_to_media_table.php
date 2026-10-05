<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media reuse (tasks/MEDIA_REUSE_PLAN.md → docs/media-architecture.md M-10).
 *
 * One image can be used in several places without being stored twice: the
 * first upload owns the file, every further usage is a lightweight "shared"
 * media row that points at the same path on the disk.
 *
 * - `shared_path`     the base path of the file a shared row resolves to
 *                     (`{prefix}/{owner-id}`) — stored on the row itself so the
 *                     URL keeps working even after the owner row is deleted.
 * - `source_media_id` the row this one was reused from (admin UI, grouping).
 * - `checksum`        sha256 of the original file, used by `media:dedupe` to
 *                     find byte-identical images that are still stored twice.
 *
 * All nullable + additive: existing rows behave exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media') || Schema::hasColumn('media', 'shared_path')) {
            return;
        }

        Schema::table('media', function (Blueprint $table): void {
            $table->string('shared_path')->nullable()->after('disk')->index();
            $table->unsignedBigInteger('source_media_id')->nullable()->after('shared_path')->index();
            $table->string('checksum', 64)->nullable()->after('source_media_id')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('media') || ! Schema::hasColumn('media', 'shared_path')) {
            return;
        }

        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex(['shared_path']);
            $table->dropIndex(['source_media_id']);
            $table->dropIndex(['checksum']);
            $table->dropColumn(['shared_path', 'source_media_id', 'checksum']);
        });
    }
};
