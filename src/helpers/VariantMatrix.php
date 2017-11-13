<?php

namespace craft\commerce\helpers;

use Craft;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\elements\Variant;
use craft\commerce\web\assets\variantmatrix\VariantMatrixAsset;
use craft\helpers\Json;

/**
 * Class VariantMatrix
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  2.0
 */
class VariantMatrix
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the HTML for a given product’s variant matrix.
     *
     * @param ProductElement $product The product model
     * @param string         $name    The input name (sans namespace). Default is 'variants'.
     *
     * @return string The variant matrix HTML
     */
    public static function getVariantMatrixHtml(ProductElement $product, $name = 'variants'): string
    {
        /** @var \craft\web\View $viewService */
        $viewService = Craft::$app->getView();
        $id = $viewService->formatInputId($name);

        $html = $viewService->renderTemplate('commerce/products/_variant_matrix', [
            'id' => $id,
            'name' => $name,
            'variants' => $product->getVariants()
        ]);

        // Namespace the name/ID for JS
        $namespacedName = $viewService->namespaceInputName($name);
        $namespacedId = $viewService->namespaceInputId($id);

        // Get the field HTML
        list($fieldBodyHtml, $fieldFootHtml) = self::_getVariantFieldHtml($product, $namespacedName);

        $viewService->registerAssetBundle(VariantMatrixAsset::class);
        $viewService->registerJs('new Craft.Commerce.VariantMatrix('.
            '"'.$namespacedId.'", '.
            Json::encode($fieldBodyHtml).', '.
            Json::encode($fieldFootHtml).', '.
            '"'.$namespacedName.'"'.
            ');');

        return $html;
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns info about each variant field type for a variant matrix.
     *
     * @param ProductElement $product The product model
     * @param string         $name    The input name (sans namespace)
     *
     * @return array
     */
    private static function _getVariantFieldHtml($product, $name): array
    {
        // Create a fake Variant model so the field types have a way to get at the owner element, if there is one
        $variant = new Variant();
        $variant->setProduct($product);

        $variantFields = $variant->getFieldLayout()->getFields();

        foreach ($variantFields as $fieldLayoutField) {
            $fieldLayoutField->setIsFresh(true);
        }

        $templatesService = Craft::$app->getView();
        $templatesService->startJsBuffer();

        $bodyHtml = $templatesService->renderTemplate('commerce/products/_variant_matrix_fields', [
            'namespace' => $name.'[__VARIANT__]',
            'variant' => $variant
        ]);

        $footHtml = $templatesService->clearJsBuffer();

        // Reset $_isFresh's
        foreach ($variantFields as $field) {
            $field->setIsFresh();
        }

        return [$bodyHtml, $footHtml];
    }
}
