<?php

namespace App\Admin\Controllers\Ext;

use App\Admin\Extensions\ExcelExpoter;
use App\Models\ExtAliexpress;

use Excel;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class AliexpressController extends Controller
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

            $content->header('Aliexpress采集');
            $content->description('任务');
            $content->body($this->grid());
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

            $content->header('项目');
            $content->description('添加');

            $content->body($this->createForm());
        });
    }

    public function store(Request $request)
    {
        $file = $request->file('file');
        Excel::load($file, function ($reader) {
            $reader->each(function ($value) {
                ExtAliexpress::create([
                    'name' => $value->name,
                ]);
            });
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ExtAliexpress::class, function (Grid $grid) {
            $grid->column('name', '名字');
            $grid->column('en_keyword', 'enKeyword');
            $grid->status('完成状态')->switch([
                'on' => ['value' => 1, 'text' => '完成', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '未完成', 'color' => 'default'],
            ]);
            $grid->actions(function ($actions) {
                $actions->disableEdit();
            });
            $grid->filter(function($filter){
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
            });
            $grid->exporter(new ExcelExpoter(['name', 'en_keyword']));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function createForm()
    {
        return Admin::form(ExtAliexpress::class, function (Form $form) {
//            $form->tools(function (Form\Tools $tools) {
//                // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
//                $tools->add('<a class="btn btn-sm btn-info" href="/admin/download?path=/static/Aliexpress_demo.csv">下载模版</a>');
//            });
            $form->file('file', '导入EXCEL');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(ExtAliexpress::class, function (Form $form) {
            $form->switch('status')->states([
                'on' => ['value' => 1, 'text' => '完成', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '未完成', 'color' => 'default'],
            ]);
        });
    }


}
