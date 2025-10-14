<?php

namespace TautId\Payment\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeTransitionsCommand extends Command
{
    public $signature = 'taut-payment:make-transitions';

    public $description = 'Create payment transition files in App/Transitions/Payment directory';

    protected array $transitions = [
        'ToCanceled',
        'ToCompleted',
        'ToDue',
        'ToPending',
    ];

    public function handle()
    {
        $path = 'app/Transitions/Payment';
        $fullPath = base_path($path);

        // Create directory if it doesn't exist
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
            $this->info("Created directory: {$path}");
        }

        foreach ($this->transitions as $transition) {
            $this->createTransitionFile($transition, $fullPath, $path);
        }

        $this->info('Payment transitions created successfully!');
    }

    protected function createTransitionFile(string $className, string $fullPath, string $relativePath): void
    {
        $filePath = $fullPath . '/' . $className . '.php';

        if (File::exists($filePath)) {
            $this->warn("File already exists: {$relativePath}/{$className}.php");
            return;
        }

        $namespace = $this->getNamespaceFromPath($relativePath);
        $stub = $this->getStub($className, $namespace);

        File::put($filePath, $stub);
        $this->info("Created: {$relativePath}/{$className}.php");
    }

    protected function getNamespaceFromPath(string $path): string
    {
        // Convert path like "app/Transitions/Payment" to "App\Transitions\Payment"
        return str_replace(['/', 'app'], ['\\', 'App'], $path);
    }

    protected function getStub(string $className, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use TautId\Payment\Abstracts\PaymentTransitionAbstract;
use TautId\Payment\Models\Payment;

class {$className} extends PaymentTransitionAbstract
{
    public function handle(Payment \$record): void
    {
        //
    }
}
";
    }
}
