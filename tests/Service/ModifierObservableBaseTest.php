<?php

require_once dirname(__FILE__) . '/GoodServiceBaseTest.php';

// Extend to test that no base functionality is screwed up

// Making abstract so I can have a child that does exactly this, and one
// that does the integration test with other modifiers
// (PHPUnit doesn't like it if I do this with a non-abstract class)

/**
 * @runTestsInSeparateProcesses
 */
abstract class GoodServiceModifierObservableBaseTest extends GoodServiceBaseTest
{

	protected function compile($types, $modifiers = null)
	{
		if ($modifiers == null)
		{
			parent::compile($types, array(new \Good\Service\ModifierObservable()));
		}
		else
		{
			parent::compile($types, $modifiers);
		}
	}
	
	public function testObserverBasics()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'int myInt');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$observable = new MyType();
		
		$observer = $this->getMock('\Good\Service\Observer');
		$observer->expects($this->once())
                 ->method('notify')
                 ->with($this->equalTo($observable));
		
		$observable->setMyInt(5);
		
		$observable->register($observer);
		
		$observable->setMyInt(7);
	}
	
	/**
	 * @depends testObserverBasics
	 */
	public function testTwoObservers()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'int myInt');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$observable = new MyType();
		
		$observer = $this->getMock('\Good\Service\Observer');
		$observer->expects($this->once())
                 ->method('notify')
                 ->with($this->equalTo($observable));
		
		$observer2 = $this->getMock('\Good\Service\Observer');
		$observer2->expects($this->once())
                  ->method('notify')
                  ->with($this->equalTo($observable));
		
		$observable->setMyInt(5);
		
		$observable->register($observer);
		$observable->register($observer2);
		
		$observable->setMyInt(7);
	}
	
	private $expecting = null;
	
	public function equalsExpecting($given)
	{
		$this->assertEquals($this->expecting, $given);
	}
	
	/**
	 * @depends testObserverBasics
	 */
	public function testTwoObservables()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'int myInt');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$observable = new MyType();
		$observable2 = new MyType();
		
		$observer = $this->getMock('\Good\Service\Observer');
		$observer->expects($this->exactly(4))
                 ->method('notify')
                 ->with($this->logicalOr($this->equalTo($observable),
										 $this->equalTo($observable2)))
				 ->will($this->returnCallback(array($this, 'equalsExpecting')));
				 
		
		$observable->setMyInt(5);
		$observable2->setMyInt(14);
		
		$observable->register($observer);
		$observable2->register($observer);
		
		$this->expecting = $observable2;
		$observable2->setMyInt(22);
		
		$this->expecting = $observable;
		$observable->setMyInt(6);
		$observable->setMyInt(7);
		
		$this->expecting = $observable2;
		$observable2->setMyInt(30);
	}
	
	/**
	 * @depends testObserverBasics
	 */
	public function testUnregisterObserver()
	{
		file_put_contents($this->inputDir . 'MyType.datatype',
							'int myInt');
		$this->compile(array('MyType' => $this->inputDir . 'MyType.datatype'));
		
		$observable = new MyType();
		
		$observer = $this->getMock('\Good\Service\Observer');
		$observer->expects($this->exactly(2))
                 ->method('notify')
                 ->with($this->equalTo($observable))
				 ->will($this->returnCallback(array($this, 'equalsExpecting')));
				 
		
		$observable->setMyInt(5213213);
		
		$observable->register($observer);
		$this->expecting = $observable;
		$observable->setMyInt(22234);
		
		$observable->unregister($observer);
		$this->expecting = null;
		$observable->setMyInt(213213);
		
		$observable->register($observer);
		$this->expecting = $observable;
		$observable->setMyInt(1235555);
	}
}

?>