<?php
namespace Setup\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOutput;
use Cake\Console\Shell;
use Cake\TestSuite\TestCase;

/**
 * Class TestCompletionStringOutput
 */
class TestWhitespaceOutput extends ConsoleOutput {

	/**
	 * @var string
	 */
	public $output = '';

	/**
	 * @param string $message
	 * @return void
	 */
	protected function _write($message) {
		$this->output .= $message;
	}

}

/**
 */
class WhitespaceShellTest extends TestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new TestWhitespaceOutput();
		$io = new ConsoleIo($this->out);

		$this->Shell = $this->getMock(
			'Setup\Shell\WhitespaceShell',
			['in', 'err', '_stop'],
			[$io]
		);

		if (!is_dir(TMP . 'whitespace')) {
			mkdir(TMP . 'whitespace', 0770, true);
		}
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Shell);
	}

	/**
	 * Test clean command
	 *
	 * @return void
	 */
	public function testClean() {
		$this->Shell->expects($this->any())->method('in')
			->will($this->returnValue('y'));

		$content = PHP_EOL . ' <?php echo $foo;' . PHP_EOL . '?> ' . PHP_EOL . PHP_EOL;
		file_put_contents(TMP . 'whitespace' . DS . 'Foo.php', $content);
		$this->Shell->runCommand(['clean', TMP . 'whitespace' . DS]);
		$output = $this->out->output;

		$this->assertTextContains('Found 1 files.', $output);
		$this->assertTextContains('found 1 leading, 1 trailing ws', $output);
		$this->assertTextContains('fixed 1 leading, 1 trailing ws', $output);

		$output = file_get_contents(TMP . 'whitespace' . DS . 'Foo.php');
		$expected = '<?php echo $foo;' . PHP_EOL . '?>';

		unlink(TMP . 'whitespace' . DS . 'Foo.php');
		$this->assertEquals($expected, $output);
	}

	/**
	 * WhitespaceShellTest::testEof()
	 *
	 * @return void
	 */
	public function testEof() {
		$this->Shell->expects($this->any())->method('in')
			->will($this->returnValue('y'));

		$content = PHP_EOL . ' <?php echo $foo;' . PHP_EOL . '?> ' . PHP_EOL . PHP_EOL;
		file_put_contents(TMP . 'whitespace' . DS . 'Foo.php', $content);
		$this->Shell->runCommand(['eof', TMP . 'whitespace' . DS]);
		$output = $this->out->output;

		//debug($output);die();

		$output = file_get_contents(TMP . 'whitespace' . DS . 'Foo.php');
		$expected = '<?php echo $foo;' . PHP_EOL;

		unlink(TMP . 'whitespace' . DS . 'Foo.php');
		$this->assertEquals($expected, $output);
	}

}
