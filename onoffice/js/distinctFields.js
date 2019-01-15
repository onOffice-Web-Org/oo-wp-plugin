var onOffice = onOffice || {};

$(function() {

		$(this).change(function(){
			doIt(this);
			});

	function renderMultiselectable(element, values, preselected)
	{
		element.children().remove();
		element.append("<input type='button' class='onoffice-multiselect-edit' value='Edit values'>");

		var instance = new onOffice.multiselect(element[0], values, preselected);
		var subElements = [].slice.call(element[0].children);
		var editButtonArray = subElements.filter(function(element) {
			return element.className === 'onoffice-multiselect-edit';
		});
		var button = editButtonArray.pop();
		button.onclick = (function(instance) {
			return function() {
				instance.show();
			};
		})(instance);
	}

	function renderNonMultiselectable(index, values)
	{
		//console.log(cleanName);
		var selectedWert = $("[name="+index+"]").val();
		var element = $("[name="+index+"]");

		if (!$.isEmptyObject(values))
		{
			element.children().remove();
			element.removeProp('disabled');
			element.append(new Option(notSpecifiedLabel[0], ''));

			$.each(values, function( key, value){
				element.append(new Option(value, key));
				if (key == selectedWert)
				{
					$("[name="+index+"] option[value="+key+"]").attr('selected', true);
				}
			});
		}
		else
		{
			element.prop('disabled', 'disabled');
		}
	}

	function doIt(myElement)
	{
		var inputValues = {};
		var multiselectSelectedValues = {};

		$('form input, form select').each(function(){

			if ($(this).is('input'))
			{
				if ($(this).is(":text") &&
					this.name !== 'range_plz' &&
					this.name !== 'range_strasse')
				{

				}
				else if ($(this).is(":checkbox"))
				{
					var theKey = this.name;

					if ($(this).is(':checked'))
					{
						if (!(theKey in inputValues))
						{
							inputValues[this.name] = [];
						}

						inputValues[this.name].push($(this).val());

						if (!(theKey in multiselectSelectedValues))
						{
							multiselectSelectedValues[this.name] = [];
						}
						multiselectSelectedValues[this.name].push($(this).val());
					}

					if (!(this.name in multiselectSelectedValues))
					{
						multiselectSelectedValues[this.name] = [];
					}
				}
				else if ($(this).is(":radio"))
				{
					if ($('input[name='+this.name+']:checked').val() == 'y')
					{
						inputValues[this.name] = '1';
					}
					else if ($('input[name='+this.name+']:checked').val() == 'n')
					{
						inputValues[this.name] = '0';
					}
				}
				else
				{
					inputValues[this.name] = $(this).val();
				}
			}

			else if ($(this).is('select'))
			{
				inputValues[this.name] = $("option:selected", this).val();
			}

		});

		$.post(base_path[0], { field: myElement.name, inputValues: JSON.stringify(inputValues), module:module[0], distinctValues: distinctValues[0] })
			.done(function( data ) {

			var dataJs = JSON.parse(data);

			$.each(dataJs, function(index, values){

				var cleanName = index.replace("[]", "");

				if ($("[data-name="+cleanName+"]").length &&
					!$.isEmptyObject(multiselectSelectedValues) &&
					!(index in multiselectSelectedValues))
				{
					multiselectSelectedValues[cleanName+"[]"] = []
				}

				if (!$.isEmptyObject(multiselectSelectedValues) &&
						index in multiselectSelectedValues)
				{
					var cleanName = index.replace("[]", "");
					var element = $("[data-name="+cleanName+"]");

					renderMultiselectable(element, values, multiselectSelectedValues[index]);
				}
				else if (index.indexOf("[]") === -1)
				{
					renderNonMultiselectable(index, values);
				}
			})
		}, 'json');
	}
});