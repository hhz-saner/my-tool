<?php

namespace App\Admin\Controllers;

use App\Models\AboutClass;

use App\Services\AboutClassServices;
use Carbon\Carbon;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class AboutClassController extends AdminController
{
    use ModelForm;

    public $aboutClassServices;

    /**
     * AboutClassController constructor.
     * @param $aboutClassServices
     */
    public function __construct(AboutClassServices $aboutClassServices)
    {
        $this->aboutClassServices = $aboutClassServices;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('约课辅助');
            $content->description('正在辅助预约列表');

            $content->body($this->grid());
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

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
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

            $content->header('约课辅助');
            $content->description('添加');

            $content->body($this->form());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(AboutClass::class, function (Grid $grid) {
            $grid->model()->where('date', '>=', Carbon::now())->where('status', 0);
            $grid->column('id', 'ID');
            $grid->column('date', '日期')->sortable();
            $grid->column('class', '课程');
            $grid->created_at('创建时间')->sortable();
            $grid->actions(function ($actions) {
                $actions->disableEdit();
            });
            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
            });
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(AboutClass::class, function (Form $form) {
            $form->select('date', '日期')->options([
                Carbon::now()->format('Y-m-d') => Carbon::now()->format('Y-m-d'),
                Carbon::now()->addDays(1)->format('Y-m-d') => Carbon::now()->addDays(1)->format('Y-m-d'),
                Carbon::now()->addDays(2)->format('Y-m-d') => Carbon::now()->addDays(2)->format('Y-m-d'),
            ])->load('class', '/admin/aboutClass/list');
            $form->select('class', '课程');
        });
    }

    public function classList(Request $request)
    {
        $date = $request->get('q');
        return $this->aboutClassServices->aboutClassList($date)->transform(function ($value) {
            return [
                'id' => $value['start_time'] . ' -- ' . $value['end_time'] . '   ' . $value['class_name'],
                'text' => $value['start_time'] . ' -- ' . $value['end_time'] . '   ' . $value['class_name'],
            ];
        });
    }
}
