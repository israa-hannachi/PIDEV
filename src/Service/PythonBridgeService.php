<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Psr\Log\LoggerInterface;

class PythonBridgeService
{
    private string $projectDir;

    public function __construct(
        private LoggerInterface $logger,
        string $projectDir
    ) {
        $this->projectDir = $projectDir;
    }

    /**
     * Executes a Python script with JSON input and returns JSON output.
     * 
     * @param string $scriptName Name of the script in the /python directory
     * @param array $args Key-value arguments to pass to the script (will be prefixed with --)
     * 
     * @return array decoded JSON result
     */
    public function executePython(string $scriptName, array $args = []): array
    {
        $scriptPath = $this->projectDir . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . $scriptName;
        
        if (!file_exists($scriptPath)) {
            throw new \Exception("Python script not found: {$scriptPath}");
        }

        $command = ['python', $scriptPath];
        
        foreach ($args as $key => $value) {
            $command[] = "--{$key}";
            $command[] = is_array($value) ? json_encode($value) : (string)$value;
        }

        $process = new Process($command);
        $process->setTimeout(60);

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->error("Python script failed: {$scriptName}", [
                    'error' => $process->getErrorOutput(),
                    'command' => $process->getCommandLine()
                ]);
                return ['success' => false, 'error' => $process->getErrorOutput()];
            }

            $output = $process->getOutput();
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error("Invalid JSON from Python: {$scriptName}", [
                    'output' => $output
                ]);
                return ['success' => false, 'error' => "Invalid JSON output from Python script"];
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Exception running Python script: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Executes a Python script by sending a JSON payload via STDIN and returns decoded JSON output.
     *
     * @param string $scriptName Name of the script in the /python directory
     * @param array  $payload   Associative array that will be JSON‑encoded and sent to the script's STDIN
     *
     * @return array Decoded JSON result or error information
     */
    public function executePythonWithInput(string $scriptName, array $payload = []): array
    {
        $scriptPath = $this->projectDir . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . $scriptName;

        if (!file_exists($scriptPath)) {
            throw new \Exception("Python script not found: {$scriptPath}");
        }

        $process = new Process(['python', $scriptPath]);
        $process->setTimeout(60);
        $process->setInput(json_encode($payload));

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->error("Python script failed (STDIN): {$scriptName}", [
                    'error' => $process->getErrorOutput(),
                    'command' => $process->getCommandLine()
                ]);
                return ['success' => false, 'error' => $process->getErrorOutput()];
            }

            $output = $process->getOutput();
            $result = json_decode($output, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error("Invalid JSON from Python (STDIN): {$scriptName}", [
                    'output' => $output
                ]);
                return ['success' => false, 'error' => "Invalid JSON output from Python script"];
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error("Exception running Python script (STDIN): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
