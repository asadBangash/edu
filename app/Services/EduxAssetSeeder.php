<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EduxAssetSeeder
{
    private const LOGO_SVG = 'edux-logo.svg';

    private const LOGO_PNG = 'edux-logo.png';

    private const FAVICON_SVG = 'edux-favicon.svg';

    private const PRINT_PNG = 'edux-logo.png';

    private const ADMIN_PNG = 'edux-logo.png';

    private const APP_PNG = 'edux-logo.png';

    private const SMALL_PNG = 'edux-small-logo.png';

    private const PLACEHOLDER_PNG = 'images.png';

    /** @var list<string> */
    private array $uploadRoots;

    public function __construct()
    {
        $this->uploadRoots = array_values(array_filter([
            base_path('edux/uploads'),
            is_dir('D:/laragon/www/edux/uploads') ? 'D:/laragon/www/edux/uploads' : null,
        ]));
    }

    /**
     * @return array{files: int, db: bool}
     */
    public function seedAll(bool $updateDatabase = true): array
    {
        $files = 0;
        $files += $this->seedSchoolLogos();
        $files += $this->seedLoginBackgrounds();
        $files += $this->seedPlaceholderImages();
        $files += $this->mirrorBundledUploads();

        $db = false;
        if ($updateDatabase) {
            $this->updateSchoolSettings();
            $this->updateFrontCmsSettings();
            $db = true;
        }

        return ['files' => $files, 'db' => $db];
    }

    public function seedSchoolLogos(): int
    {
        $srcLogo = base_path('edux/backend/img/edux/'.(is_file(base_path('edux/backend/img/edux/'.self::LOGO_PNG)) ? self::LOGO_PNG : self::LOGO_SVG));
        $srcIcon = base_path('edux/backend/img/edux/'.self::FAVICON_SVG);

        if (! is_file($srcLogo) || ! is_file($srcIcon)) {
            throw new \RuntimeException('Missing logo sources under edux/backend/img/edux/');
        }

        $dirs = [
            'school_content/admin_logo',
            'school_content/admin_small_logo',
            'school_content/logo',
            'school_content/logo/app_logo',
        ];

        $count = 0;
        foreach ($this->uploadRoots as $root) {
            foreach ($dirs as $rel) {
                $dir = $root.'/'.$rel;
                if (! is_dir($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                $logoName = basename($srcLogo);
                File::copy($srcLogo, $dir.'/'.$logoName);
                if ($logoName !== self::LOGO_SVG && is_file(base_path('edux/backend/img/edux/'.self::LOGO_SVG))) {
                    File::copy(base_path('edux/backend/img/edux/'.self::LOGO_SVG), $dir.'/'.self::LOGO_SVG);
                    $count++;
                }
                File::copy($srcIcon, $dir.'/'.self::FAVICON_SVG);
                $count += 2;
            }

            $this->writeBrandLogos($root);
            $count += 4;
        }

        return $count;
    }

    public function seedLoginBackgrounds(): int
    {
        $srcDir = base_path('edux/uploads/school_content/login_image');
        if (! is_dir($srcDir)) {
            return 0;
        }

        $count = 0;
        foreach (glob($srcDir.'/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $file) {
            $name = basename($file);
            foreach ($this->uploadRoots as $root) {
                $destDir = $root.'/school_content/login_image';
                if (! is_dir($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }
                $dest = $destDir.'/'.$name;
                if (! is_file($dest)) {
                    File::copy($file, $dest);
                    $count++;
                }
            }
        }

        return $count;
    }

    public function seedPlaceholderImages(): int
    {
        $tmp = storage_path('app/edux-placeholder.png');
        $this->renderPlaceholderPng($tmp, 290, 120, 'EduX');

        $targets = [
            'school_content/logo/'.self::PLACEHOLDER_PNG,
            'school_content/admin_logo/'.self::PLACEHOLDER_PNG,
            'school_content/admin_small_logo/'.self::PLACEHOLDER_PNG,
            'school_content/logo/app_logo/'.self::PLACEHOLDER_PNG,
        ];

        $count = 0;
        foreach ($this->uploadRoots as $root) {
            foreach ($targets as $rel) {
                $dest = $root.'/'.$rel;
                $dir = dirname($dest);
                if (! is_dir($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                File::copy($tmp, $dest);
                $count++;
            }
        }

        if (is_file($tmp)) {
            @unlink($tmp);
        }

        return $count;
    }

    /**
     * Copy default student/staff images into secondary upload root if present.
     */
    public function mirrorBundledUploads(): int
    {
        $primary = base_path('edux/uploads');
        $count = 0;

        foreach ($this->uploadRoots as $root) {
            if ($root === $primary) {
                continue;
            }
            foreach (['student_images', 'staff_images'] as $folder) {
                $src = $primary.'/'.$folder;
                if (! is_dir($src)) {
                    continue;
                }
                $dest = $root.'/'.$folder;
                foreach (glob($src.'/*') ?: [] as $file) {
                    if (! is_file($file)) {
                        continue;
                    }
                    if (! is_dir($dest)) {
                        File::makeDirectory($dest, 0755, true);
                    }
                    $target = $dest.'/'.basename($file);
                    if (! is_file($target)) {
                        File::copy($file, $target);
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    public function updateSchoolSettings(): void
    {
        $loginBg = $this->resolveLoginBackgroundFilename();

        $payload = [
            'image' => self::PRINT_PNG,
            'admin_logo' => self::ADMIN_PNG,
            'admin_small_logo' => self::SMALL_PNG,
            'app_logo' => self::APP_PNG,
            'theme_color' => '#1565D8',
            'theme_type' => 'custom',
        ];

        if ($loginBg !== null) {
            $payload['admin_login_page_background'] = $loginBg;
            $payload['user_login_page_background'] = $loginBg;
        }

        DB::table('sch_settings')->where('id', 1)->update($payload);
    }

    public function updateFrontCmsSettings(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('front_cms_settings')) {
            return;
        }

        $logoPath = is_file(base_path('edux/backend/img/edux/'.self::LOGO_PNG))
            ? 'backend/img/edux/'.self::LOGO_PNG
            : 'backend/img/edux/'.self::LOGO_SVG;

        DB::table('front_cms_settings')->where('id', 1)->update([
            'logo' => $logoPath,
            'fav_icon' => 'backend/img/edux/'.self::FAVICON_SVG,
        ]);
    }

    private function writeBrandLogos(string $uploadRoot): void
    {
        $banner = base_path('edux/backend/img/edux/'.self::LOGO_PNG);
        $map = [
            'school_content/logo/'.self::PRINT_PNG,
            'school_content/admin_logo/'.self::ADMIN_PNG,
            'school_content/logo/app_logo/'.self::APP_PNG,
        ];

        foreach ($map as $rel) {
            $path = $uploadRoot.'/'.$rel;
            $dir = dirname($path);
            if (! is_dir($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            if (is_file($banner)) {
                File::copy($banner, $path);
            } else {
                $this->renderLogoPng($path, 290, 51, false);
            }
        }

        $small = $uploadRoot.'/school_content/admin_small_logo/'.self::SMALL_PNG;
        if (! is_dir(dirname($small))) {
            File::makeDirectory(dirname($small), 0755, true);
        }
        $this->renderLogoPng($small, 32, 32, true);
    }

    private function resolveLoginBackgroundFilename(): ?string
    {
        $dir = base_path('edux/uploads/school_content/login_image');
        if (! is_dir($dir)) {
            return null;
        }

        $preferred = '1662796099-1140705836631c4143b6dc3!login_bg6.jpg';
        if (is_file($dir.'/'.$preferred)) {
            return $preferred;
        }

        $files = glob($dir.'/*.jpg') ?: [];

        return $files !== [] ? basename($files[0]) : null;
    }

    private function writeGeneratedPngs(string $uploadRoot): void
    {
        $map = [
            'school_content/logo/'.self::PRINT_PNG => [340, 200, false],
            'school_content/admin_logo/'.self::ADMIN_PNG => [290, 51, false],
            'school_content/logo/app_logo/'.self::APP_PNG => [290, 51, false],
            'school_content/admin_small_logo/'.self::SMALL_PNG => [32, 32, true],
        ];

        foreach ($map as $rel => [$w, $h, $small]) {
            $path = $uploadRoot.'/'.$rel;
            $dir = dirname($path);
            if (! is_dir($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            $this->renderLogoPng($path, $w, $h, $small);
        }
    }

    private function renderLogoPng(string $path, int $width, int $height, bool $compact): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $fallback = base_path('edux/uploads/school_content/logo/1.png');
            if (is_file($fallback)) {
                File::copy($fallback, $path);
            }

            return;
        }

        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $blue = imagecolorallocate($im, 30, 64, 175);
        $cyan = imagecolorallocate($im, 6, 182, 212);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        if ($compact) {
            imagefilledrectangle($im, 0, 0, $width, $height, $blue);
            $font = 3;
            $text = 'X';
            $tw = imagefontwidth($font) * strlen($text);
            $th = imagefontheight($font);
            imagestring($im, $font, (int) (($width - $tw) / 2), (int) (($height - $th) / 2), $text, $cyan);
        } else {
            $font = $height <= 60 ? 4 : 5;
            $text = 'EduX';
            $tw = imagefontwidth($font) * strlen($text);
            $th = imagefontheight($font);
            imagestring($im, $font, (int) (($width - $tw) / 2), (int) (($height - $th) / 2), $text, $blue);
        }

        imagepng($im, $path);
        imagedestroy($im);
    }

    private function renderPlaceholderPng(string $path, int $width, int $height, string $label): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $fallback = base_path('edux/uploads/school_content/logo/1.png');
            if (is_file($fallback)) {
                File::copy($fallback, $path);
            }

            return;
        }

        $im = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($im, 241, 245, 249);
        $border = imagecolorallocate($im, 203, 213, 225);
        $text = imagecolorallocate($im, 100, 116, 139);
        imagefilledrectangle($im, 0, 0, $width, $height, $bg);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);
        $font = 4;
        $tw = imagefontwidth($font) * strlen($label);
        $th = imagefontheight($font);
        imagestring($im, $font, (int) (($width - $tw) / 2), (int) (($height - $th) / 2), $label, $text);
        imagepng($im, $path);
        imagedestroy($im);
    }
}