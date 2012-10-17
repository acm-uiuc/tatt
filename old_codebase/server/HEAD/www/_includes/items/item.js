$(document).ready(function(){
  $(".confirm").click(function(event){
    if (!confirm("Are you sure that you want to delete this?"))
       event.preventDefault();
  })
});

