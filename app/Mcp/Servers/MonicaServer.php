<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\EditContactTool;
use App\Mcp\Tools\ListContactTool;
use App\Mcp\Tools\StoreContactTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Monica Server')]
#[Version('0.0.1')]
#[Instructions('Manages personal contacts.')]
class MonicaServer extends Server
{
    protected array $tools = [
        StoreContactTool::class,
        EditContactTool::class,
        ListContactTool::class
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
