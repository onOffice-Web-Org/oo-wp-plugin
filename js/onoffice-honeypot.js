jQuery(document).ready(function($){
	if(postTitles['tmpField'] == true){
        var input = $("<input>").attr("type", "text")
                               .attr("class", "tmpField")
                               .attr("name", "tmpField");
        $("#onoffice-form").prepend(input);
    }
});