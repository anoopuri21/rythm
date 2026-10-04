<?php

declare(strict_types=1);

namespace Tests\Feature;

use FilesystemIterator;
use Illuminate\Support\Facades\Blade;
use ParseError;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Tests\TestCase;

/**
 * A Blade template that fails to compile only blows up when the page renders,
 * so it can sit undetected in a view that no test happens to visit.
 *
 * The trap this guards against: Alpine/Vue shorthand attributes that collide
 * with a Blade directive name. `@error="broken = true"` (an <img> error
 * listener) is compiled as Blade's `@error` directive, which emits `if (...):`
 * without a matching `endif` and takes the whole view down with a ParseError.
 * The fix is to escape it (`@@error=`) or spell it out (`x-on:error=`).
 */
final class ViewCompilationTest extends TestCase
{
    public function test_every_blade_template_compiles_into_valid_php(): void
    {
        $failures = [];

        foreach ($this->bladeTemplates() as $path) {
            try {
                token_get_all(Blade::compileString(file_get_contents($path)), TOKEN_PARSE);
            } catch (ParseError $e) {
                $failures[] = $this->relativePath($path).': '.$e->getMessage();
            }
        }

        $this->assertSame(
            [],
            $failures,
            "Blade templates that do not compile into valid PHP:\n".implode("\n", $failures)
        );
    }

    public function test_html_attributes_do_not_reuse_blade_directive_names(): void
    {
        $directives = $this->bladeDirectives();
        $collisions = [];

        foreach ($this->bladeTemplates() as $path) {
            $source = file_get_contents($path);

            // An unescaped `@name` followed by `=` or `.` is an HTML/JS event
            // attribute (Alpine shorthand) wearing a Blade directive's name.
            preg_match_all('/(?<![\w@])@([A-Za-z_]\w*)[=.]/', $source, $matches);

            foreach ($matches[1] as $index => $name) {
                if (in_array($name, $directives, true)) {
                    $collisions[] = $this->relativePath($path).': '.$matches[0][$index];
                }
            }
        }

        $this->assertSame(
            [],
            $collisions,
            "Blade directive names used as HTML attributes — escape them as @@name or write x-on:name:\n"
            .implode("\n", $collisions)
        );
    }

    /**
     * Every directive name Blade will act on: compiled methods plus runtime-registered ones.
     *
     * @return list<string>
     */
    private function bladeDirectives(): array
    {
        $compiler = app('blade.compiler');

        $names = array_keys($compiler->getCustomDirectives());

        foreach ((new ReflectionClass($compiler))->getMethods() as $method) {
            $method = $method->getName();

            if (str_starts_with($method, 'compile') && strlen($method) > 7) {
                $names[] = lcfirst(substr($method, 7));
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return list<string>
     */
    private function bladeTemplates(): array
    {
        $paths = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views'), FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $paths[] = $file->getPathname();
            }
        }

        sort($paths);

        return $paths;
    }

    private function relativePath(string $path): string
    {
        return str_starts_with($path, base_path())
            ? ltrim(str_replace(base_path(), '', $path), '/')
            : $path;
    }
}
