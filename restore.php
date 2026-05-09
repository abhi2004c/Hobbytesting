<?php

$log_file = "/home/sangam/.gemini/antigravity/brain/e74474c6-1c56-4cc7-9d32-ded42a474304/.system_generated/logs/overview.txt";
$lines = file($log_file);
$restored = 0;

foreach ($lines as $line) {
    $data = json_decode($line, true);
    if ($data && isset($data['type']) && $data['type'] === 'PLANNER_RESPONSE' && isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $call) {
            if (isset($call['name']) && $call['name'] === 'write_to_file') {
                $args = $call['args'] ?? [];
                $target = trim($args['TargetFile'] ?? '', '"');
                $content = $args['CodeContent'] ?? '';
                
                if (strpos($target, 'app/Filament/') !== false) {
                    if (strpos($content, '"') === 0) {
                        $content = json_decode($content);
                    }
                    
                    // Apply fixes for Filament v3
                    $content = str_replace(
                        'protected static ?string $navigationGroup', 
                        'protected static string|\UnitEnum|null $navigationGroup', 
                        $content
                    );
                    $content = str_replace(
                        'protected static ?string $navigationIcon', 
                        'protected static string|\Illuminate\Contracts\Support\Htmlable|null $navigationIcon', 
                        $content
                    );
                    
                    if (!is_dir(dirname($target))) {
                        mkdir(dirname($target), 0755, true);
                    }
                    file_put_contents($target, $content);
                    echo "Restored: $target\n";
                    $restored++;
                }
            }
        }
    }
}
echo "Total restored: $restored\n";
