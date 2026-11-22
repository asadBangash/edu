<?php

namespace App\Http\Controllers;

use App\Support\SmartSchoolPaths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SmartSchoolAssetController extends Controller
{
    public function backend(string $path): BinaryFileResponse
    {
        return $this->serveFrom(SmartSchoolPaths::runtimeRoot().'/backend', $path);
    }

    public function uploads(string $path): BinaryFileResponse
    {
        return $this->serveFrom(SmartSchoolPaths::uploadsRoot(), $path);
    }

    public function themeCss(): BinaryFileResponse
    {
        $file = SmartSchoolPaths::runtimeRoot().'/backend/theme_css/custom.css';
        if (! is_file($file)) {
            abort(404);
        }

        return response()->file($file, ['Content-Type' => 'text/css']);
    }

    private function serveFrom(string $root, string $path): BinaryFileResponse
    {
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $file = $root.'/'.$path;
        if (! is_file($file)) {
            abort(404);
        }

        return response()->file($file);
    }
}
