<?php
/**
 * Created by PhpStorm.
 * User: saner
 * Date: 2017/10/10
 * Time: 下午9:54
 */

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class PopoverQrCode extends AbstractDisplayer
{
    public function display($placement = 'left')
    {
        Admin::script("$('.grid-qrcode').popover({
    html: true,
    trigger: 'focus'
})");
        return <<<EOT
<a class='btn btn-default btn-sm grid-qrcode' 
data-placement="$placement" 
tabindex="0"
data-content="<img src='https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$this->value}' style='height: 150px;width: 150px;'/>">
<i class="fa fa-qrcode"></i>

</a>
EOT;
    }

}
