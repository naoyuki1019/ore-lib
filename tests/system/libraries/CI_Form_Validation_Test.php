<?php

namespace ore\tests\system\libraries;

use ore\CI_Form_validation;
use ore\tests\oreTestCase;

require_once dirname(dirname(dirname(__FILE__))).'/oreTestCase.php';

/**
 * Class CI_Form_Validation_Test
 *
 * @package ore
 */
class CI_Form_Validation_Test extends oreTestCase {

	/**
	 *
	 */
	public function testIsNatural() {
		$v = new CI_Form_validation();
		$this->assertTrue($v->is_natural(0));
		$this->assertTrue($v->is_natural(1));
		$this->assertFalse($v->is_natural(-1));
		$this->assertFalse($v->is_natural('1.1.1.1.1'));
	}
}
