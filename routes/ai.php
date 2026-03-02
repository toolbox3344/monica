<?php

use App\Mcp\Servers\MonicaServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', MonicaServer::class)
    ->middleware('auth:sanctum');
//A5yVagx96Uluwens8kJ7RVUjZme1nSH9CDLJ36av5ac700b9
