<?php
class ImageProcessor {
    
    /**
     * Resize and optimize image
     */
    public static function processImage($source_path, $destination_path, $options = []) {
        $default_options = [
            'width' => 800,
            'height' => 600,
            'quality' => 80,
            'crop' => false
        ];
        
        $options = array_merge($default_options, $options);
        
        // Get image info
        $image_info = getimagesize($source_path);
        if (!$image_info) {
            return false;
        }
        
        $mime_type = $image_info['mime'];
        
        // Create image from source
        switch ($mime_type) {
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
            default:
                return false;
        }
        
        if (!$source_image) {
            return false;
        }
        
        $src_width = imagesx($source_image);
        $src_height = imagesy($source_image);
        
        // Calculate new dimensions
        if ($options['crop']) {
            $new_width = $options['width'];
            $new_height = $options['height'];
            
            $src_ratio = $src_width / $src_height;
            $new_ratio = $new_width / $new_height;
            
            if ($new_ratio > $src_ratio) {
                $temp_height = $new_height;
                $temp_width = $new_height * $src_ratio;
            } else {
                $temp_width = $new_width;
                $temp_height = $new_width / $src_ratio;
            }
            
            $temp_image = imagecreatetruecolor($temp_width, $temp_height);
            imagecopyresampled($temp_image, $source_image, 0, 0, 0, 0, $temp_width, $temp_height, $src_width, $src_height);
            
            $dx = ($temp_width - $new_width) / 2;
            $dy = ($temp_height - $new_height) / 2;
            
            $new_image = imagecreatetruecolor($new_width, $new_height);
            imagecopy($new_image, $temp_image, 0, 0, $dx, $dy, $new_width, $new_height);
            imagedestroy($temp_image);
            
        } else {
            // Resize maintaining aspect ratio
            $new_ratio = $options['width'] / $options['height'];
            $src_ratio = $src_width / $src_height;
            
            if ($src_ratio > $new_ratio) {
                $new_width = $options['width'];
                $new_height = $options['width'] / $src_ratio;
            } else {
                $new_height = $options['height'];
                $new_width = $options['height'] * $src_ratio;
            }
            
            $new_image = imagecreatetruecolor($new_width, $new_height);
            
            // Preserve transparency for PNG and GIF
            if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
                imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
            }
            
            imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $src_width, $src_height);
        }
        
        // Save processed image
        $result = false;
        switch ($mime_type) {
            case 'image/jpeg':
                $result = imagejpeg($new_image, $destination_path, $options['quality']);
                break;
            case 'image/png':
                $result = imagepng($new_image, $destination_path, 9 - round($options['quality'] / 10));
                break;
            case 'image/gif':
                $result = imagegif($new_image, $destination_path);
                break;
        }
        
        // Clean up
        imagedestroy($source_image);
        imagedestroy($new_image);
        
        return $result;
    }
    
    /**
     * Create multiple image sizes
     */
    public static function createImageSizes($source_path, $filename, $sizes = []) {
        $results = [];
        
        foreach ($sizes as $size_name => $dimensions) {
            $destination_path = dirname($source_path) . '/' . $size_name . '_' . $filename;
            
            $options = [
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'crop' => isset($dimensions[2]) ? $dimensions[2] : false
            ];
            
            if (self::processImage($source_path, $destination_path, $options)) {
                $results[$size_name] = $size_name . '_' . $filename;
            }
        }
        
        return $results;
    }
    
    /**
     * Generate thumbnail from image
     */
    public static function createThumbnail($source_path, $destination_path, $size = 200) {
        return self::processImage($source_path, $destination_path, [
            'width' => $size,
            'height' => $size,
            'crop' => true,
            'quality' => 85
        ]);
    }
    
    /**
     * Optimize image file size
     */
    public static function optimizeImage($image_path, $quality = 75) {
        $image_info = getimagesize($image_path);
        if (!$image_info) return false;
        
        $mime_type = $image_info['mime'];
        
        switch ($mime_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($image_path);
                $optimized = imagejpeg($image, $image_path, $quality);
                imagedestroy($image);
                return $optimized;
                
            case 'image/png':
                $image = imagecreatefrompng($image_path);
                $optimized = imagepng($image, $image_path, 9 - round($quality / 10));
                imagedestroy($image);
                return $optimized;
        }
        
        return false;
    }
    
    /**
     * Add watermark to image
     */
    public static function addWatermark($source_path, $watermark_path, $position = 'bottom-right') {
        $main_image = imagecreatefromstring(file_get_contents($source_path));
        $watermark = imagecreatefromstring(file_get_contents($watermark_path));
        
        if (!$main_image || !$watermark) {
            return false;
        }
        
        $main_width = imagesx($main_image);
        $main_height = imagesy($main_image);
        $watermark_width = imagesx($watermark);
        $watermark_height = imagesy($watermark);
        
        // Calculate position
        switch ($position) {
            case 'top-left':
                $x = 10;
                $y = 10;
                break;
            case 'top-right':
                $x = $main_width - $watermark_width - 10;
                $y = 10;
                break;
            case 'bottom-left':
                $x = 10;
                $y = $main_height - $watermark_height - 10;
                break;
            case 'bottom-right':
            default:
                $x = $main_width - $watermark_width - 10;
                $y = $main_height - $watermark_height - 10;
                break;
        }
        
        // Apply watermark
        imagecopy($main_image, $watermark, $x, $y, 0, 0, $watermark_width, $watermark_height);
        
        // Save watermarked image
        $result = imagejpeg($main_image, $source_path, 90);
        
        // Clean up
        imagedestroy($main_image);
        imagedestroy($watermark);
        
        return $result;
    }
}
?>