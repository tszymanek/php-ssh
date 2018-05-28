<?php

namespace Ssh;

use Ssh\Exception\RuntimeException;
use function preg_match;
use function preg_replace;
use function ssh2_exec;
use function ssh2_fetch_stream;
use function stream_get_contents;
use function stream_set_blocking;
use const SSH2_STREAM_STDERR;
use const SSH2_TERM_UNIT_CHARS;

/**
 * Wrapper for ssh2_exec
 *
 * @author Cam Spiers <camspiers@gmail.com>
 * @author Greg Militello <junk@thinkof.net>
 * @author Gildas Quéméner <gildas.quemener@gmail.com>
 */
class Exec extends Subsystem
{
    private $error;

    protected function createResource()
    {
        $this->resource = $this->getSessionResource();
    }

    public function run(
        $cmd,
        bool $checkReturnCode = true,
        $pty = null,
        array $env = [],
        $width = 80,
        $height = 25,
        $widthHeightType = SSH2_TERM_UNIT_CHARS
    ) {
        if ($checkReturnCode) {
            $cmd .= ';echo -ne "[return_code:$?]"';
        }

        $stdout = ssh2_exec($this->getResource(), $cmd, $pty, $env, $width, $height, $widthHeightType);
        $stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);
        stream_set_blocking($stderr, true);
        stream_set_blocking($stdout, true);

        $error = stream_get_contents($stderr);
        if ($error !== '') {
            $this->error = $error;
        }

        $output = stream_get_contents($stdout);
        if ($checkReturnCode === false) {
            return $output;
        }

        preg_match('/\[return_code:(.*?)\]/', $output, $match);
        if ((int) $match[1] !== 0) {
            throw new RuntimeException(stream_get_contents($stderr), (int) $match[1]);
        }

        return preg_replace('/\[return_code:(.*?)\]/', '', $output);
    }

    public function getError() : string
    {
        return $this->error;
    }
}
