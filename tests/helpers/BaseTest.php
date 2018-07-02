<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;
use Versatile\Attributes\UsesVirtualAttributes;
use Versatile\Attributes\Dispatcher;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{

    public function mockAttributesDispatcher()
    {
        return m::mock('Versatile\Attributes\Dispatcher');
    }

    protected function newVirtualAttributesModel()
    {
        return new VirtualAttributesModel();
    }

    protected function mockModel()
    {
        return m::mock('Illuminate\Database\Eloquent\Model');
    }


    public function tearDown()
    {
        m::close();
    }

}

class VirtualAttributesModel extends Model
{
    use UsesVirtualAttributes;

    public $virtualAttributes = [
        'show_in_menu' => 'bitmask:visibility,1'
    ];
}
