<?php

namespace Lyra\AdminBundle\Tests\Renderer;

use Lyra\AdminBundle\Renderer\FormRenderer;

class FormRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $factory = $this->getMockFormFactory();
        $renderer = new FormRenderer($factory);

        $metadata = array(
            'id' => array(
                'name' => 'id',
                'type' => 'integer',
                'id' => true
            ),
            'test' => array(
                'name' => 'test',
                'type' => 'text'
            ),
        );

        $renderer->setMetadata($metadata);

        $options = array(
            'fields' => array(),
            'form' => array(
                'groups' => array(),
                'new' => array('groups' => array())
            )
        );

        $renderer->setOptions($options);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'test' => array(
                'name' => 'test',
                'type' => 'text',
                'form' => null
            )
        ), $renderer->getFields());

        $this->assertEquals(array(
            'main' => array(
                'caption' => null,
                'break_after' => false,
                'fields' => array('test')
                )
            ), $renderer->getGroups());
    }

    public function testMergeFields()
    {
        $factory = $this->getMockFormFactory();
        $renderer = new FormRenderer($factory);

        $metadata = array(
            'test-1' => array(
                'name' => 'test-1',
                'type' => 'integer',
            ),
            'test-2' => array(
                'name' => 'test-2',
                'type' => 'text'
            ),
        );

        $renderer->setMetadata($metadata);

        $options = array(
            'fields' => array(
                'test-1' => array('label' => 'test'),
                'test-2' => array('type' => 'string')
            ),
            'form' => array(
                                'groups' => array(),
                'new' => array('groups' => array())
            )
        );

        $renderer->setOptions($options);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'test-1' => array(
                'name' => 'test-1',
                'type' => 'integer',
                'form' => null,
                'label' => 'test'
            ),
            'test-2' => array(
                'name' => 'test-2',
                'type' => 'string',
                'form' => null,
            )
        ), $renderer->getFields());
    }

    public function testMergeGroups()
    {
        $factory = $this->getMockFormFactory();
        $renderer = new FormRenderer($factory);

        $metadata = array(
            'test-1' => array(
                'name' => 'test-1',
                'type' => 'integer',
            ),
            'test-2' => array(
                'name' => 'test-2',
                'type' => 'text'
            ),
        );

        $renderer->setMetadata($metadata);

        $options = array(
            'fields' => array(),
            'form' => array(
                'groups' => array('main' => array(
                    'fields' => array('test-1', 'test-2')
                )),
                'new' => array('groups' => array())
            )
        );

        $renderer->setOptions($options);
        $renderer->setAction('new');

        $this->assertEquals(array(
            'main' => array(
                'fields' =>  array('test-1', 'test-2')
            )
        ), $renderer->getGroups());

        $options['form']['new']['groups'] = array('main' => array(
            'fields' => array('test-1')
            ));

        $renderer->setOptions($options);

        $this->assertEquals(array(
            'main' => array(
                'fields' =>  array('test-1')
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
