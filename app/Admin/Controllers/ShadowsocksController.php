<?php

namespace App\Admin\Controllers;

use App\Events\SsChange;
use App\Models\Shadowsocks;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ShadowsocksController extends AdminController
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('影梭账号');
            $content->description('列表');
            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Shadowsocks::class, function (Grid $grid) {
            $grid->user()->name();
            $grid->column('port', '端口号')->sortable();
            $grid->column('password', '密码');
            $grid->column('qrCode', '生成二维码')->display(function () {
                return 'ss://' . base64_encode('aes-256-cfb:' . $this->password . '@' . config('services.vpn.host') . ':' . $this->port);
//                return QrCode::encoding('UTF-8')->size(250)->generate($qrcode);
            })->popoverQrCode('right');
            $grid->column('description', '备注');
            $grid->created_at('创建时间');
            $grid->updated_at('修改时间');

            $grid->disableFilter();
            $grid->disableExport();
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('影梭账号');
            $content->description('修改');
            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        return Admin::form(Shadowsocks::class, function (Form $form) use ($id) {
            $form->hidden('user_id')->value(Admin::user()->id);
            if ($id == null) {
                $form->select('port', '端口号')->options(route('shadowsocks.unusedPort'));
            } else {
                $form->select('port', '端口号')->options(route('shadowsocks.unusedPort', ['port' => Shadowsocks::find($id)->port]));
            }
            $form->text('password', '密码')->rules('required|min:5|max:20');
            $form->text('description', '描述');
            $form->saved(function () {
                event(new SsChange());
            });
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('影梭账号');
            $content->description('创建');
            $content->body($this->form());
        });
    }

    public function destroy($id)
    {
        if ($this->form()->destroy($id)) {
            event(new SsChange());
            return response()->json([
                'status' => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    public function unusedPort($port = null)
    {
        return collect(range(50001, 50500))->push(443)->diff(Shadowsocks::withTrashed()->where('port', '!=', $port)->pluck('port'))->values();
    }

}
