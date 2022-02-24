<?php

$className = "mandarinBank";
$paymentName = "MandarinBank";

include "standalone.php";

$objectTypesCollection = umiObjectTypesCollection::getInstance();
$objectsCollection = umiObjectsCollection::getInstance();

// получаем родительский тип
$parentTypeId = $objectTypesCollection->getTypeIdByGUID("emarket-payment");

// Тип для внутреннего объекта, связанного с публичным типом
$internalTypeId = $objectTypesCollection->getTypeIdByGUID("emarket-paymenttype");
$typeId = $objectTypesCollection->addType($parentTypeId, $paymentName);

// Создаем внутренний объект
$internalObjectId = $objectsCollection->addObject($paymentName, $internalTypeId);
$internalObject = $objectsCollection->getObject($internalObjectId);
$internalObject->setValue("class_name", $className); // имя класса для реализации

// связываем его с типом
$internalObject->setValue("payment_type_id", $typeId);
$internalObject->setValue("payment_type_guid", "user-emarket-payment-" . $typeId);
$internalObject->commit();

//Добавим группу настроек
$type  = umiObjectTypesCollection::getInstance()->getType($typeId);
$groupId = $type->addFieldsGroup("payment_props_mandarin", "Настройки", true, true);
$type->setFieldGroupOrd($groupId, 0, false);

//Добавим поля в группу настроек
if($type){
    $fieldsCollection = umiFieldsCollection::getInstance();
    $group = $type->getFieldsGroupByName('payment_props_mandarin');


    $fieldId = $fieldsCollection->addField('merchantid', 'MerchantId', 2);
    $group->attachField($fieldId);
    $fieldId = $fieldsCollection->addField('secret', 'Секретный ключ банка', 2);
    $group->attachField($fieldId);
}
// Связываем внешний тип и внутренний объект
$type = $objectTypesCollection->getType($typeId);
$type->setGUID($internalObject->getValue("payment_type_guid"));
$type->commit();

if(!is_file('/classes/components/emarket/payment/system/mandarinBank.php')){
    file_get_contents('/classes/components/emarket/payment/system/mandarinBank.php','<?php ?>');
}
echo "Готово!";


?>
