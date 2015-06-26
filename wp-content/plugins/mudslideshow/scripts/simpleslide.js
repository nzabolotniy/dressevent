 function doPrevious(id,slideImages) {
   var actualPic=document.getElementById('actualPic'+id).value;
   if (actualPic > 0) {
      actualPic--;
      var div=document.getElementById('simple_gallery'+id);
      div.style.backgroundImage="url('"+slideImages[actualPic]+"')";
   }
   document.getElementById('actualPic'+id).value=actualPic;
   arrows(id,slideImages);
   changeText(id,slideImages);
   return actualPic;
 }

 function doNext(id,slideImages) {
    var actualPic=document.getElementById('actualPic'+id).value;
    if (actualPic < slideImages.length-1) {
       actualPic++;
       var div=document.getElementById('simple_gallery'+id);
       div.style.backgroundImage="url('"+slideImages[actualPic]+"')";
    }
    document.getElementById('actualPic'+id).value=actualPic;
    arrows(id,slideImages);
    changeText(id,slideImages);
    return actualPic;
 }
      
 function arrows(id,slideImages) {
    var path=document.getElementById('path'+id).value;
    var actualPic=document.getElementById('actualPic'+id).value;
               
    var nextslide = document.getElementById('nextslide'+id);
    var backslide = document.getElementById('backslide'+id);
        
    if (actualPic == slideImages.length-1) {
       nextslide.src=path+"rightarrow-no.png";
    } else {
       nextslide.src=path+"rightarrow.png";
    }
        
    if (actualPic == 0) {
       backslide.src=path+"leftarrow-no.png";
    } else {
       backslide.src=path+"leftarrow.png";
    }
 }
            
 function changeText(id,slideImages) {
    var actualPic=document.getElementById('actualPic'+id).value;
    var text=document.getElementById('slidemeter'+id);
    var to_show=(actualPic*1)+1;
    text.innerHTML=to_show+'/'+slideImages.length;
 }
 
 function preloadImages(slideImages) {
    if (document.images)
    {
       preload_image_object = new Image();

       var i = 0;
       for(i=0; i<=slideImages.length; i++) 
         preload_image_object.src = slideImages[i];
    }
 }
