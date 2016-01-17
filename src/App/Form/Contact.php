<?php

namespace App\Form;

use Aura\Input\Form;



class Contact extends Form
{
    public function init()
    {

        $this->setField('first_name', 'text')
            ->setAttribs(array(
                'id' => 'first_name',
                'size' => 20,
                'maxlength' => 20,
            ));

        $this->setField('message', 'text')
            ->setAttribs(array(
                'id' => 'message',
                'size' => 200,
                'maxlength' => 200,
            ));



        // フィルタオブジェクトを取得します。
        $filter = $this->getFilter();
        // フィルタをセットします。
            $filter->addSoftRule('first_name', $filter::IS, 'string');
            $filter->addSoftRule('first_name', $filter::IS, 'strlenBetween', 6, 12);

//        $filter->addSoftRule('message', $filter::IS, 'string');
//        $filter->addSoftRule('message', $filter::IS, 'strlenMin', 6);
//        $filter->useFieldMessage('first_name', 'hogehoge');

    }
}