var notInTags=['a', 'head', 'noscript', 'option', 'script', 'style', 'title', 'textarea'];
var res = document.evaluate("//text()[not(ancestor::"+notInTags.join(') and not(ancestor::')+")]",
	document, null,	XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null); 
var i, el, l, m, p, span, txt, 
	urlRE=/((?:https?|ftp):\/\/[^\s'"'<>()]*|[-\w.+]+@(?:[-\w]+\.)+[\w]{2,6})/gi;

for (i=0; el=res.snapshotItem(i); i++) {
	//grab the text of this element and be sure it has a URL in it
	txt=el.textContent;
	span=null;
	p=0;
	while (m=urlRE.exec(txt)) {
		if (null==span) {
			//create a span to hold the new text with links in it
			span=document.createElement('span');
		}
                
		//get the link without trailing dots
		l=m[0].replace(/\.*$/, '');
                
                // if it's the logged in user
                if ( typeof(logged_in_user) != 'undefined' && l == logged_in_user ) {
                    // skip
                    continue;
                }
                
		//put in text up to the link
		span.appendChild(document.createTextNode(txt.substring(p, m.index)));
		//create a link and put it in the span
		a=document.createElement('a');
		a.appendChild(document.createTextNode(l));
		if (-1==l.indexOf('://')) { l='mailto:'+l; }
		a.setAttribute('href', l);
		span.appendChild(a);
		p=m.index+m[0].length;
	}
	if (span) {
		//take the text after the last link
		span.appendChild(document.createTextNode(txt.substring(p, txt.length)));
		//replace the original text with the new span
		el.parentNode.replaceChild(span, el);
	}
}
