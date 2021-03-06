<?php


namespace App;


use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class Main
{

	const VERSION = "0.2.0";

	/**
	 * @var \Monolog\Logger
	 */
	public $log;

	/**
	 * @var \App\Runtime
	 */
	public $runtime;

	/**
	 * @var \App\Exec\IExecutor
	 */
	public $executor;

	private $arguments = [];


	/**
	 * Main constructor.
	 *
	 * @param $arguments
	 */
	public function __construct($arguments)
	{
		$this->arguments = $arguments;

		$this->executor = new Exec\Executor();

		/* log */
		$this->log = new Logger('main');
		$streamHandler = new Log\StreamHandler('php://output');

		$datetimeFormat = "Y-m-d H:i:s"
		. (version_compare(PHP_VERSION, '7.0.0') >= 0 ? '.v' : null);

		$formatter = new LineFormatter(
			"[%datetime%] %channel%-%level_name%: %message% %context% %extra%\n",
			$datetimeFormat,
			true,
			true
		);
		$streamHandler->setFormatter($formatter);
		$this->log->pushHandler($streamHandler);


	}


	public function run()
	{
		$this->log->info('* * * Mishell v' . self::VERSION . ' is starting...');


		try
		{
			$this->runtime = new Runtime($this->arguments);
			$this->checkGit();
			$this->innerFunctionality();
		}
		catch (MiException $e)
		{
			$this->log->log(
				$e->getCode()
				,
				$e->getMessage()

			);
		}
		catch (\Exception $e)
		{
			$this->log->emergency(
				'Uncaught exception: ' . $e->getMessage(),
				(array) $e->getTraceAsString()
			);
		}
		$this->log->info('* * * ...ending Mishell');
	}


	/* privates */


	private function innerFunctionality()
	{
		while (true)
		{
			foreach ($this->runtime->profile as $profile)
			{
				$runner = new Profile\ProfileRunner(
					$profile,
					$this->log->withName('profile'),
					$this->runtime,
					$this->executor
				);
				$runner->runProfile();

				chdir($this->runtime->workingDirectory);
			}


			if (!$this->runtime->daemon) return;

			$this->log->debug("...waiting...");
			sleep(60);
		}

	}


	private function checkGit()
	{
		$version = $this->executor->execute('git --version');

		if ($version->code)
		{
			throw new MiException('git not available', Logger::ALERT);
		}
	}
}
