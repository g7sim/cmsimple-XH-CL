var newStyle = document.createElement("link");
newStyle.rel = "stylesheet";
newStyle.href = "http://my-css-file.css";
document.getElementsByTagName("head")[0].appendChild(newStyle);


 var css = document.createElement('style');            
 css.type = 'text/css';     
       if (css.styleSheet)
                css.styleSheet.cssText = styles;
            else
                css.appendChild(document.createTextNode(styles));
             
            /* Append style to the tag name */
            document.getElementsByTagName("head")[0].appendChild(css);
        }
----------------------------------------------------------------------------------
		
JavaScript nachladen
    newscript=document.createElement('script');
    newscript.type="text/javascript";
    newscript.src=url;
    newscript.onload=function(){};
    document.getElementsByTagName("head")[0].appendChild(newscript);
	
Style nachladen
    newstyle=document.createElement('link');
    newstyle.rel="stylesheet";
    newstyle.type="text/css";
    newstyle.href=url;
    newstyle.onload=function(){};
    document.getElementsByTagName("head")[0].appendChild(newstyle);
--------------------------------------------------------------------------------------

let cssText = ` @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;900&display=swap'); `;

let myStyleTag = document.createElement('style');
myStyleTag.type = 'text/css';

myStyleTag.appendChild(document.createTextNode(cssText));
document.getElementsByTagName('head')[0].appendChild('myStyleTag');

el.setAttribute('style', el.getAttribute('style')+'; color: red');

-----------------------------------------------------------------------------------------------------
< script type="text/javascript">
// <![CDATA[
var newStyle = document.createElement("style");
newStyle.innerHTML = 
    ".course-content .activity_list .content .summary>.no-overflow ul>li::before, .course-content .section .content .summary>.no-overflow ul>li::before {"+
    "content: ' ' !important;"+
    "}";
document.getElementsByTagName("head")[0].appendChild(newStyle);
// ]]>
</script>
-----------------------------------------------------------------------------------