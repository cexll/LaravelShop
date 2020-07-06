<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\CouponCode;

class CouponCodesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '优惠券列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CouponCode());
        $grid->model()->orderBy('created_at', 'desc');
        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('名称'));
        $grid->column('code', __('优惠码'));
        $grid->column('description', '描述');
        $grid->column('usage', '用量')->display(function ($value) {
            return "{$this->used} / {$this->total}";
        });
        $grid->column('enabled', __('是否启用'))->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->column('created_at', __('创建时间'));

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CouponCode::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('code', __('Code'));
        $show->field('type', __('Type'));
        $show->field('value', __('Value'));
        $show->field('total', __('Total'));
        $show->field('used', __('Used'));
        $show->field('min_amount', __('Min amount'));
        $show->field('not_before', __('Not before'));
        $show->field('not_after', __('Not after'));
        $show->field('enabled', __('Enabled'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CouponCode());

        $form->display('id', 'ID');
        $form->text('name', __('名称'))->rules('required');
        $form->text('code', __('优惠码'))->rules(function ($form) {
            if ($id = $form->model()->id) {
                return 'nullable|unique:coupon_codes,code,'.$id.',id';
            } else {
                return 'nullable|unique:coupon_codes';
            }
        });
        $form->radio('type', __('类型'))->options(CouponCode::$typeMap)->rules('required')->default(CouponCode::TYPE_FIXED);
        $form->decimal('value', __('折扣'))->rules(function ($form) {
            if ($form->type === CouponCode::TYPE_PERCENT) {
                // 如果选中了百分比折扣类型, 那么折扣范围只能是1 ~ 99
                return 'required|numeric|between:1,99';
            } else {
                // 否则只要大等于0.01即可
                return 'required|numeric|min:0.01';
            }
        });
        $form->number('total', '总量')->rules('required|numeric:min:0');
        $form->decimal('min_amount', __('最低金额'))->rules('required|numeric|min:0');
        $form->datetime('not_before', __('开始时间'));
        $form->datetime('not_after', __('结束时间'));
        $form->switch('enabled', __('启用'))->options(['1'=>'是', '0'=>'否']);

        $form->saving(function (Form $form) {
            if (!$form->code) {
                $form->code = CouponCode::findAvailableCode();
            }
        });

        return $form;
    }
}
