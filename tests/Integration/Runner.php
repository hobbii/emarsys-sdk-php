<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Throwable;

final class Runner
{
    private const EXIT_SUCCESS = 0;

    private const EXIT_FAILURE = 1;

    /** @var array<string,class-string> Map of test names to test classes */
    private array $availableTests;

    private string $clientId;

    private string $clientSecret;

    /**
     * @param  array<string,class-string>  $availableTests  Map of test names to test classes
     * @param  string  $clientId  Emarsys client ID
     * @param  string  $clientSecret  Emarsys client secret
     */
    public function __construct(array $availableTests, string $clientId, string $clientSecret)
    {
        $this->availableTests = $availableTests;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Run integration tests based on provided arguments
     *
     * @param  array  $argv  Command line arguments
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function run(array $argv): int
    {
        // Parse command line arguments
        $parsedArgs = $this->parseArguments($argv);
        $testName = $parsedArgs['test'] ?? null;

        if ($testName === null) {
            $this->echoUsageInfo();

            return self::EXIT_SUCCESS;
        }

        try {
            echo "🧪 Emarsys SDK Integration Test Runner\n";
            echo "=====================================\n\n";

            $client = new Client($this->clientId, $this->clientSecret);

            echo "✅ Client created successfully\n";

            $tests = $this->createTests($testName, $client);

            foreach ($tests as $test) {
                echo 'Running Test: '.get_class($test)."...\n\n";
                $test->run($parsedArgs);
                echo "\nDone.\n";
            }

            return self::EXIT_SUCCESS;
        } catch (AuthenticationException $e) {
            echo "❌ Authentication failed\n";
            echo "💡 Please check your client_id and client_secret.\n";
            $this->echoExceptionDetails($e);

            return self::EXIT_FAILURE;
        } catch (ApiException $e) {
            echo "❌ API error\n";
            $this->echoExceptionDetails($e);

            return self::EXIT_FAILURE;
        } catch (Throwable $e) {
            $this->echoExceptionDetails($e);

            return self::EXIT_FAILURE;
        }
    }

    /**
     * Create test instances based on test name and arguments
     *
     * @param  string  $testName  Name of the test to run (predefined name or file path)
     * @param  Client  $client  Emarsys client instance
     * @return array Array of test instances
     */
    private function createTests(string $testName, Client $client): array
    {
        $tests = [];
        $availableTestNames = array_keys($this->availableTests);

        if (in_array($testName, $availableTestNames)) {
            // Use predefined test
            $tests = [$this->availableTests[$testName]];
        } elseif ($this->isValidTestFilePath($testName)) {
            // Use test file path - convert to class name
            $className = $this->filePathToClassName($testName);
            if ($className && class_exists($className)) {
                $tests = [$className];
            } else {
                echo "❌ Could not load test class from file: {$testName}\n\n";
                $this->echoUsageInfo();
                exit(self::EXIT_FAILURE);
            }
        } else {
            echo "❌ Unknown test: {$testName}\n\n";
            echo "💡 Use a predefined test name or a valid file path.\n";
            $this->echoUsageInfo();
            exit(self::EXIT_FAILURE);
        }

        return array_map(fn (string $testClass) => new $testClass($client), $tests);
    }

    /**
     * Parse command line arguments into associative array
     *
     * @param  array  $argv  Command line arguments
     * @return array Parsed arguments
     */
    private function parseArguments(array $argv): array
    {
        $result = [];

        // First argument (after script name) is the test name
        if (isset($argv[1]) && ! str_contains($argv[1], '=')) {
            $result['test'] = $argv[1];
            $startIndex = 2;
        } else {
            $startIndex = 1;
        }

        // Parse key=value parameters
        for ($i = $startIndex; $i < count($argv); $i++) {
            if (str_contains($argv[$i], '=')) {
                [$key, $value] = explode('=', $argv[$i], 2);
                $result[$key] = $value;
            } elseif (! isset($result['test'])) {
                // If no test specified yet, treat as test name
                $result['test'] = $argv[$i];
            }
        }

        return $result;
    }

    /**
     * Display usage information
     */
    private function echoUsageInfo(): void
    {
        echo "Available tests:\n";
        echo "  - quick         : Quick connection test (read-only)\n";
        echo "\n";
        echo "Usage:\n";
        echo "  php run-integration-tests.php quick\n";
        echo "  php run-integration-tests.php tests/Integration/QuickConnectionTest.php\n";
        echo "  php run-integration-tests.php [test-name] [email=user@example.com]\n";
        echo "\n";
        echo "Note: You can use predefined test names or file paths.\n\n";
    }

    /**
     * Check if the given string is a valid test file path
     *
     * @param  string  $testName  Test name to check
     * @return bool True if it's a valid file path
     */
    private function isValidTestFilePath(string $testName): bool
    {
        return file_exists($testName) && str_ends_with($testName, '.php');
    }

    /**
     * Convert file path to class name using PSR-4 conventions
     *
     * @param  string  $filePath  Path to the PHP file
     * @return string|null Class name or null if cannot be determined
     */
    private function filePathToClassName(string $filePath): ?string
    {
        $realPath = realpath($filePath);
        if (! $realPath) {
            return null;
        }

        // Include the file to ensure the class is loaded
        require_once $realPath;

        // Extract relative path from project root
        $projectRoot = dirname(__DIR__, 2); // Go up from tests/Integration to project root
        $relativePath = str_replace($projectRoot.'/', '', $realPath);

        // Convert file path to namespace and class name
        if (str_starts_with($relativePath, 'tests/')) {
            // Remove tests/ prefix and .php suffix
            $classPath = substr($relativePath, 6, -4);
            // Convert path separators to namespace separators
            $classPath = str_replace('/', '\\', $classPath);

            // Build full class name
            return "Hobbii\\Emarsys\\Tests\\{$classPath}";
        }

        return null;
    }

    /**
     * Display credentials error message
     */
    private function echoCredentialsError(): void
    {
        echo "🚨 Emarsys credentials not found!\n\n";
        echo "Please set your credentials first:\n";
        echo "export EMARSYS_CLIENT_ID='your-client-id'\n";
        echo "export EMARSYS_CLIENT_SECRET='your-client-secret'\n\n";
        echo "Or create a .env file based on .env.example\n\n";
    }

    /**
     * Display exception details recursively
     *
     * @param  Throwable  $e  Exception to display
     */
    private function echoExceptionDetails(Throwable $e): void
    {
        echo '❌ '.(string) $e."\n";

        if ($e->getPrevious() !== null) {
            echo "\nCaused by:\n";
            $this->echoExceptionDetails($e->getPrevious());
        }
    }
}
