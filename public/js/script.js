document.addEventListener('ready',function(){
   let inputsOfPrices = document.querySelectorAll('.product-input-price'),
       token = document.querySelector('[name="csrf-token"]').getAttribute('content'),
       timeoutInputValue = 0;

   for (let i = 0; i < inputsOfPrices.length -1; i++) {
       inputsOfPrices[i].addEventListener('input', function () {
           listenerForInput(this);
       });
   };

   let listenerForInput = function (input) {
       if (timeoutInputValue) {
           clearTimeout(timeoutInputValue);
       }

       timeoutInputValue = setTimeout(async () => {
           $.ajaxSetup({
               headers: { 'X-CSRF-TOKEN': token}
           });

           $.ajax({
               url:  '/product/edit/' + input.getAttribute('data-id'),
               method: 'POST',
               dataType: 'json',
               data: {'price' : input.value},
               success: function (data) {
                   if (!!data.success){
                       alert(data.success);
                   }
               },
               fail: (function(data) {
                   if (!!data.errors){
                       alert(data.errors);
                   }
               })
           });

       }, 1000)
   };
});