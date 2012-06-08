var BrowserUpgrade = {

	Notify: function() {
		this.needupgrade = false;
		switch (BrowserDetect.browser) {
			case "Firefox": if (BrowserDetect.version < 3) this.needupgrade = true; break
	        case "Safari": 
				{ 
					if (BrowserDetect.version < 5 && BrowserDetect.OS!="iPhone/iPod") 
						this.needupgrade = true;
					else if(BrowserDetect.version < 4)
						this.needupgrade = true;
				}
				break
			case "Explorer": if (BrowserDetect.version < 7) this.needupgrade = true; break
			case "Opera": if (BrowserDetect.version <= 10) this.needupgrade = true; break
        }
	}
};
BrowserUpgrade.Notify();
