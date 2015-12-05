window.onload=init;
function init(){
	if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion.match(/7./i)=="7.") 
		{ 
			//alert("IE 7.0"); 
		}else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion.match(/8./i)=="8.") 
		{ 
			//alert("IE 8.0"); 
		}else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion.match(/9./i)=="9.") 
		{ 
			//alert("IE 9.0"); 
			nav_url();
		}else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE6.0"){ 
			//alert("IE 6.0"); parentNode.removeChild
		}else if(navigator.appName == "Microsoft Internet Explorer" && navigator.appVersion .split(";")[1].replace(/[ ]/g,"")=="MSIE5.0"){ 
			//alert("IE 5.0"); 
		}else{
			nav_url();
		}

}

function nav_url(){
	var dpp=document.getElementById("nav").childNodes[1].childNodes;
	var len=dpp.length;
	var v1=location.href;
	var len3=v1.length;
	var v2=v1.substring(0,len3-1);//去除链接字符窜的最后一个字符

	for(var i=1;i<len;i+=2){
		var cl=dpp[i].childNodes[0].href;
		if(v1==cl || v2==cl){
			dpp[i].className="current2";
		}else{
			dpp[i].className="current";
		}
	}
}