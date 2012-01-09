<?php

namespace Lyra\AdminBundle\Tests\Renderer;

use Lyra\AdminBundle\Renderer\FormRenderer;
use Lyra\AdminBundle\Configuration\AdminConfiguration;

class FormRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroups()
    {
        $factory = $this->getMockFormFactory();

        $options = array(
            'fields' => array(
                'test' => array(
                    'name' => 'test',
                    'type' => 'text'
                ),
            ),
            'form' => array(
                'groups' => array(),
                'new' => array('groups' => array())
            )
        );
        $configuration = new AdminConfiguration($options);
        $renderer = new FormRenderer($factory, $configuration);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'main' => array(
                'caption' => null,
                'break_after' => false,
                'fields' => array('test')
                )
            ), $renderer->getGroups());
    }

    public function testMergeGroups()
    {
        $factory = $this->getMockFormFactory();

        $options = array(
            'fields' => array(
                'test-1' => array(
                    'name' => 'test-1',
                    'type' => 'integer',
                ),
                'test-2' => array(
                    'name' => 'test-2',
                    'type' => 'text'
                ),
            ),
            'form' => array(
                'groups' => array('main' => array(
                    'fields' => array('test-1', 'test-2')
                )),
                'new' => array('groups' => array()),
                'edit' => array('groups' => array())
            )
        );

        $configuration = new AdminConfiguration($options);
        $renderer = new FormRenderer($factory, $configuration);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'main' => array(
                'fields' =>  array('test-1', 'test-2')
            )
        ), $renderer->getGroups());

        $options['form']['new']['groups'] = array('main' => array(
            'fields' => array('test-1')
            ));

        $configuration = new AdminConfiguration($options);
        $renderer = new FormRenderer($factory, $configuration);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'main' => array(
                'fields' =>  array('test-1')
            )
        ), $renderer->getGroups());

        $configuration = new AdminConfiguration($options);
        $renderer = new FormRenderer($factory, $configuration);
        $renderer->setAction('edit');

        $this->assertEquals(array(
            'main' => array(
                'fields' =>  array('test-1', 'test-2')
            )
        ), $renderer->getGroups());
    }

    protected function getMockFormFactory()
    {
        return
            $this->getMockBuilder('Lyra\AdminBundle\FormFactory\AdminFormFactory')
                ->disableOriginalConstructor()
                ->getMock();
    }
}
