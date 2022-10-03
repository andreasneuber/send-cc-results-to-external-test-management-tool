<?php

namespace Helper;

use Codeception\Module;

class ConfigHelper extends Module
{
	public function getConfig(string $key): ?string
	{
		return $this->config[$key] ?? null;
	}
}