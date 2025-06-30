<?php

/**
 *
 *    Copyright (C) 2016  onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormPost;

include(ONOFFICE_PLUGIN_DIR.'/templates.dist/fields.php');
$displayError = false;
add_thickbox();

$addressValues = array();
$miscValues = array();
$hiddenValues = array();

$showFormAsModal = $pForm->getShowFormAsModal() || $pForm->getFormStatus() === FormPost::MESSAGE_SUCCESS;

if ($pForm->getFormStatus() === FormPost::MESSAGE_SUCCESS) {
	echo '<p role="status">'.esc_html__('Thank you for your inquiry. We will get back to you as soon as possible.', 'onoffice-for-wp-websites').'</p>';
} else {
	if ($pForm->getFormStatus() === FormPost::MESSAGE_ERROR) {
		echo '<p role="status">'.esc_html__('An error has occurred. Please check your details.', 'onoffice-for-wp-websites').'</p>';
	} elseif ($pForm->getFormStatus() === \onOffice\WPlugin\FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
		echo '<p role="status">'.esc_html__('Not all mandatory fields have been filled out. Please check your entries.', 'onoffice-for-wp-websites').'</p>';
		$displayError = true;
	} elseif ($pForm->getFormStatus() === onOffice\WPlugin\FormPost::MESSAGE_RECAPTCHA_SPAM) {
		echo '<p role="status">'.esc_html__('Spam recognized!', 'onoffice-for-wp-websites').'</p>';
	}

	/* @var $pForm Form */
	foreach ( $pForm->getInputFields() as $input => $table ) {
		if ($pForm->isHiddenField($input)) {
			$hiddenValues []= renderFormField($input, $pForm);
			continue;
		}
		if ( $pForm->isMissingField( $input )  &&
			$pForm->getFormStatus() == FormPost::MESSAGE_REQUIRED_FIELDS_MISSING) {
			/* translators: %s will be replaced with a translated field name. */
			echo sprintf(__('Please enter a value for %s.', 'onoffice-for-wp-websites'), esc_html($pForm->getFieldLabel( $input ))).'<br>';
		}

		$isRequired = $pForm->isRequiredField($input);
		$addition   = $isRequired ? '<span class="oo-visually-hidden">'.esc_html__('Pflichtfeld', 'onoffice-for-wp-websites').'</span><span aria-hidden="true">*</span>' : '';
		$isHiddenField = $pForm->isHiddenField($input);
		$label = $pForm->getFieldLabel($input).' '.$addition;

		if (\onOffice\WPlugin\Types\FieldTypes::FIELD_TYPE_SINGLESELECT== $pForm->getFieldType($input)) {

			$line = '<div class="oo-single-select"><label for="'.$input.'-ts-control"><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$pForm->getFieldLabel($input).' '.$addition.'</span></label>';
			$line .=  renderFormField($input, $pForm).'</div>';	

		} else {
			$line = '<label><span class="oo-label-text' . ($displayError && $isRequired ? ' displayerror' : '') . '">'.$pForm->getFieldLabel($input).' '.$addition;
			$line .= renderFormField($input, $pForm).'</span></label>';		
		}

	
		$pageNumber = $pForm->getPagePerForm($input);
		if (!isset($addressValues[$pageNumber])) {
			$addressValues[$pageNumber] = array();
		}
		$addressValues[$pageNumber][] = $line;
	}
}
?>

<script>
	jQuery(document).ready(function() {
		var oOPaging = new onOffice.paging('leadform-<?php echo sanitize_title($pForm->getFormId()); ?>');
		oOPaging.setFormId('leadgeneratorform-<?php echo sanitize_title($pForm->getFormId()); ?>');
		oOPaging.setup();
	});
</script>

<div id="onoffice-lead-<?php echo sanitize_title($pForm->getFormId()); ?>" <?php echo $showFormAsModal ? 'style="display:none;"' : ''; ?>>
		<form name="leadgenerator" action="" method="post" id="leadgeneratorform-<?php echo sanitize_title($pForm->getFormId()); ?>"  class="oo-form" novalidate>
			<input type="hidden" name="oo_formid" value="<?php echo esc_attr($pForm->getFormId()); ?>">
			<input type="hidden" name="oo_formno" value="<?php echo esc_attr($pForm->getFormNo()); ?>">

			<?php 
			$firstRequired = false;
			$hasRequiredFields = false;

			foreach ($pForm->getInputFields() as $input => $table) {
				if (
					$pForm->isRequiredField($input)
				) {
					$hasRequiredFields = true;
					break;
				}
			}
			if ($hasRequiredFields) {
				echo '<div class="oo-form-required" aria-hidden="true">' . esc_html__('* Mandatory fields', 'onoffice-for-wp-websites') . '</div>';
			} ?>
			<div id="leadform-<?php echo sanitize_title($pForm->getFormId()); ?>">
				<?php
					if ($pForm->getFormStatus() === FormPost::MESSAGE_ERROR) {
						echo esc_html__('ERROR!', 'onoffice-for-wp-websites');
					}
				?>

                <?php
                $pageTitles = [];

                for ($i = 1; $i <= 6; $i++) {
                    $pageTitles[$i] = esc_html__('Page ' . $i, 'onoffice-for-wp-websites');
                }

                $totalPages = count($addressValues);

                foreach ($addressValues as $pageNumber => $fields): ?>
                    <div class="lead-lightbox lead-page-<?php echo $pageNumber; ?>">
                        <?php if($totalPages > 1): ?>
                            <span><?php echo sprintf('%s', $pageTitles[$pageNumber]); ?></span>
                        <?php endif; ?>
                            <?php echo implode('', $fields); ?>
                        <?php if ($pageNumber == $totalPages): ?>
                            <div style="float:right">
                                <?php
                                $pForm->setGenericSetting('formId', 'leadgeneratorform-' . sanitize_title($pForm->getFormId()));
                                include(ONOFFICE_PLUGIN_DIR.'/templates.dist/form/formsubmit.php');
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
				<?php echo implode($hiddenValues); ?>
				<span class="leadform-back" style="float:left; cursor:pointer;" role="button">
					<?php echo esc_html__('Back', 'onoffice-for-wp-websites'); ?>
				</span>
				<?php if ($totalPages !== 1): ?>
				<span class="leadform-forward" style="float:right; cursor:pointer;" role="button">
					<?php echo esc_html__('Next', 'onoffice-for-wp-websites'); ?>
				</span>
				<?php endif; ?>
			</div>
		</form>
</div>

<?php

if (in_array($pForm->getFormStatus(), [
		null,
		FormPost::MESSAGE_ERROR,
		FormPost::MESSAGE_REQUIRED_FIELDS_MISSING,
	]) && $pForm->getShowFormAsModal()) {
	echo '<a href="#TB_inline?width=700&height=650&inlineId=onoffice-lead-' . sanitize_title($pForm->getFormId()) . '" target="_top" class="thickbox oo-leadformlink">';
	echo esc_html__('Open the Form', 'onoffice-for-wp-websites');
	echo '</a>';
}