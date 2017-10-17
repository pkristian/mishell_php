<?php


namespace App;


use App\Log\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class Main
{

	/**
	 * @var \Monolog\Logger
	 */
	public $log;

	/**
	 * @var \App\Runtime
	 */
	public $runtime;


	/**
	 * Main constructor.
	 *
	 * @param $arguments
	 */
	public function __construct($arguments)
	{
		/* log */
		$this->log = new Logger('main');
		$streamHandler = new StreamHandler('php://output');
		$formatter = new LineFormatter(
			"[%datetime%] %channel%-%level_name%: %message% %context% %extra%\n",
			"Y-m-d H:i:s.v",
			true,
			true
		);
		$streamHandler->setFormatter($formatter);
		$this->log->pushHandler($streamHandler);

		/* runtime */
		$this->runtime = new Runtime($arguments);
	}


	public function run()
	{
		$this->log->info('* * * Mishell is starting...');

		try
		{
			$this->innerFunctionality();
		}
		catch (\Exception $e)
		{
			$this->log->err(
				$e->getMessage(),
				[
					$e->getFile() . ':' . $e->getLine(),
				]
			);
		}
		$this->log->info('* * * ...ending Mishell');
	}


	/* privates */


	private function innerFunctionality()
	{
		$runner = new ProfileRunner(
			$this->log->withName('profile'),
			$this->runtime,
			new Executor()
		);
		$runner->runProfile();
	}
}
