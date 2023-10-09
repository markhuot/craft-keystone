<?php

namespace markhuot\keystone\listeners;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\fieldlayoutelements\CustomField;
use craft\helpers\Cp;
use yii\base\Event;

class OverrideDraftResponseWithFieldHtml
{
    public static array $override = [];

    public static function override(ElementInterface $element, FieldInterface $field)
    {
        foreach ($element->getFieldLayout()->createForm($element)->tabs as $tab) {
            if (!$tab->getUid()) {
                continue;
            }

            foreach ($tab->elements as [$fieldLayout, $isConditional, $fieldHtml]) {
                if ($fieldLayout instanceof CustomField) {
                    if ($fieldLayout->getField()->handle === $field->handle) {
                        static::$override[] = [
                            'elementId' => $element->id,
                            'tabUid' => $tab->uid,
                            'fieldUid' => $fieldLayout->uid,
                            'fieldHtml' => $fieldHtml,
                        ];
                    }
                }
            }
        }
    }

    public function handle(Event $event)
    {
        if (\Craft::$app->request->getBodyParam('action') !== 'elements/save-draft') {
            return;
        }

        /** @var \craft\web\Response $response */
        $response = $event->sender;
        $json = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);

        foreach ($json['missingElements'] as $tabIndex => $missingTab) {
            $tabOverrides = collect(static::$override)->where(fn ($override) => $override['tabUid'] === $missingTab['uid']);
            if ($tabOverrides->count()) {
                foreach ($missingTab['elements'] as $fieldIndex => $missingField) {
                    $fieldOverrides = $tabOverrides->where(fn ($override) => $override['fieldUid'] === $missingField['uid']);
                    if ($fieldOverrides->count()) {
                        if ($fieldOverrides->first()['elementId'] === $json['element']['id']) {
                            // $json['missingElements'][0]['elements'][1]['html'] = '<div id="fields-myKeystoneField-field" class="field width-100" data-attribute="myKeystoneField" data-type="markhuot\keystone\fields\Keystone" data-layout-element="3acbff61-c6f9-4163-b49e-5f085e6e08d1" tabindex="-1"><div class="heading"><label id="fields-myKeystoneField-label" for="myKeystoneField">My Keystone Field</label></div><div class="input ltr">' .
                            //     $fieldOverrides->first()['fieldHtml']
                            //     . '</div></div>';
                            $json['missingElements'][$tabIndex]['elements'][$fieldIndex]['html'] = $fieldOverrides->first()['fieldHtml'];
                        }
                    }
                }
            }
        }

        $response->content = json_encode($json);
    }
}
