<?php

namespace App\Services;

use App\Models\Nas;
use Exception;

class MikrotikService
{
    private $socket;
    private int $timeout = 5;

    public function connect(Nas $nas): bool
    {
        $host = $nas->api_host ?: $nas->nasname;
        $port = $nas->api_port ?: 8728;

        $this->socket = @fsockopen($host, $port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            throw new Exception("Cannot connect to MikroTik at {$host}:{$port} - {$errstr}");
        }

        return $this->login($nas->api_username, $nas->api_password);
    }

    private function login(string $username, string $password): bool
    {
        $this->write('/login', false);
        $this->write('=name=' . $username, false);
        $this->write('=password=' . $password);

        $response = $this->read();

        return isset($response[0]) && $response[0] === '!done';
    }

    public function disconnectUser(Nas $nas, string $username, string $serviceType = 'hotspot'): bool
    {
        try {
            $this->connect($nas);

            if ($serviceType === 'hotspot') {
                $this->write('/ip/hotspot/active/print', false);
                $this->write('?user=' . $username);
                $response = $this->read();

                foreach ($response as $line) {
                    if (preg_match('/=\.id=(.+)/', $line, $matches)) {
                        $this->write('/ip/hotspot/active/remove', false);
                        $this->write('=.id=' . $matches[1]);
                        $this->read();
                    }
                }
            } else {
                $this->write('/ppp/active/print', false);
                $this->write('?name=' . $username);
                $response = $this->read();

                foreach ($response as $line) {
                    if (preg_match('/=\.id=(.+)/', $line, $matches)) {
                        $this->write('/ppp/active/remove', false);
                        $this->write('=.id=' . $matches[1]);
                        $this->read();
                    }
                }
            }

            $this->disconnect();
            return true;
        } catch (Exception) {
            $this->disconnect();
            return false;
        }
    }

    public function getSystemInfo(Nas $nas): array
    {
        try {
            $this->connect($nas);

            $this->write('/system/resource/print');
            $resource = $this->read();

            $this->write('/system/identity/print');
            $identity = $this->read();

            $this->disconnect();

            return [
                'resource' => $this->parseResponse($resource),
                'identity' => $this->parseResponse($identity),
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function write(string $command, bool $end = true): void
    {
        $data = $this->encodeLength(strlen($command)) . $command;
        fwrite($this->socket, $data);

        if ($end) {
            fwrite($this->socket, chr(0));
        }
    }

    private function read(): array
    {
        $response = [];
        $receivedDone = false;

        while (!$receivedDone) {
            $byte = ord(fread($this->socket, 1));

            $length = 0;
            if ($byte < 0x80) {
                $length = $byte;
            } elseif ($byte < 0xC0) {
                $length = (($byte & 0x3F) << 8) + ord(fread($this->socket, 1));
            } elseif ($byte < 0xE0) {
                $length = (($byte & 0x1F) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            } elseif ($byte < 0xF0) {
                $length = (($byte & 0x0F) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
            }

            if ($length > 0) {
                $line = '';
                $remaining = $length;
                while ($remaining > 0) {
                    $chunk = fread($this->socket, $remaining);
                    $line .= $chunk;
                    $remaining -= strlen($chunk);
                }
                $response[] = $line;

                if ($line === '!done') {
                    $receivedDone = true;
                }
            } else {
                $receivedDone = true;
            }
        }

        return $response;
    }

    private function encodeLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        } elseif ($length < 0x4000) {
            return chr(($length >> 8) | 0x80) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            return chr(($length >> 16) | 0xC0) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } else {
            return chr(($length >> 24) | 0xE0) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }
    }

    private function parseResponse(array $response): array
    {
        $items = [];
        $current = [];

        foreach ($response as $line) {
            if ($line === '!re') {
                if (!empty($current)) {
                    $items[] = $current;
                }
                $current = [];
            } elseif (str_starts_with($line, '=')) {
                $parts = explode('=', substr($line, 1), 2);
                if (count($parts) === 2) {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }

        if (!empty($current)) {
            $items[] = $current;
        }

        return $items;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
