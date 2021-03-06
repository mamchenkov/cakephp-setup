<?php
namespace Setup\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\Folder;

/**
 * Shell to remove CakePHP copyright from APP files.
 *
 * @author Mark Scherer
 * @license MIT
 */
class CopyrightRemovalShell extends Shell {

	/**
	 * @return int|null
	 */
	public function clean() {
		if (!empty($this->args[0])) {
			$folder = realpath($this->args[0]);
		} else {
			$folder = APP;
		}
		$App = new Folder($folder);
		$this->out('Cleaning copyright notices in ' . $folder);

		$ext = '.*';
		if (!empty($this->params['ext'])) {
			$ext = $this->params['ext'];
		}
		$files = $App->findRecursive('.*\.' . $ext);
		$this->out('Found ' . count($files) . ' files.');

		$count = 0;
		foreach ($files as $file) {
			$this->out('Processing ' . $file, 1, Shell::VERBOSE);

			$content = $original = file_get_contents($file);
			$content = preg_replace('/\<\?php\s*\s+\/\*\*\s*\s+\* CakePHP.*\*\//msUi', '<?php', $content);

			if ($content === $original) {
				continue;
			}

			$count++;

			if (empty($this->params['dry-run'])) {
				file_put_contents($file, $content);
			}
		}

		$this->out('--------');
		$this->out($count . ' files fixed.');
	}

	public function getOptionParser() {
		$subcommandParser = [
			'options' => [
				'ext' => [
					'short' => 'e',
					'help' => 'Specify extensions [php|...], defaults to [php|ctp].',
					'default' => '',
				],
				'dry-run' => [
					'short' => 'd',
					'help' => 'Dry run the clear command, no files will actually be modified. Should be combined with verbose!',
					'boolean' => true
				],
			]
		];

		return parent::getOptionParser()
			->description('This Shell removes unnecessary CakePHP copyright notices (only allowed for your application code!).')
			->addSubcommand('clean', [
				'help' => 'Detect and remove any CakePHP copyright notices from APP files.',
				'parser' => $subcommandParser
			]);
	}

}
