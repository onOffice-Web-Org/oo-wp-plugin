$(function() {

		$(this).change(function(){
			doIt(this);
			});



	function doIt(myElement)
	{
		var inputValues = {};

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
					if($(this).is(':checked'))
					{
						var theKey = this.name;

						if (!(theKey in inputValues))
						{
							inputValues[this.name] = [];
						}
						inputValues[this.name].push($(this).val());
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

		$.post(base_path, { field: myElement.name, inputValues: JSON.stringify(inputValues), module:module, distinctValues: distinctValues })
			.done(function( data ) {

			var dataJs = JSON.parse(data);

			$.each(dataJs, function(index, values){


				var selectedWert = $("[name="+index+"]").val();

				if (!$.isEmptyObject(values))
				{
					$("[name="+index+"]").children().remove();
					$("[name="+index+"]").removeProp('disabled');
					$("[name="+index+"]").append(new Option(notSpecifiedLabel, ''));

					$.each(values, function( key, value){
						$("[name="+index+"]").append(new Option(value, key));
						if (key == selectedWert)
						{
							$("[name="+index+"] option[value="+key+"]").attr('selected', true);
						}
					});
				}
				else
				{
					$("[name="+index+"]").prop('disabled', 'disabled');
				}
			})
		}, 'json');
	}
});