jQuery(document).ready(function ($) {
    const mainForm = $('#onoffice-form');
  
    mainForm.on('submit', function(event) {
      if (mainForm.data('submitted')) {
        event.preventDefault();
        return;
      }
      disableFormSubmission();
    });
  
    function disableFormSubmission() {
      mainForm.data('submitted', true);
    }
});
