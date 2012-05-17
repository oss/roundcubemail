var BrowserUpgrade = {

	Notify: function() {
		
		this.needupgrade = false;

		switch (BrowserDetect.browser) {
			case "Firefox": if (BrowserDetect.version <= 3) this.needupgrade = true; break
	                case "Safari": if (BrowserDetect.version <= 3) this.needupgrade = true; break
			case "Explorer": if (BrowserDetect.version <= 8) this.needupgrade = true; break
			case "Opera": if (BrowserDetect.version <= 10) this.needupgrade = true; break
			default: this.needupgrade = true;
                }
	}
};
BrowserUpgrade.Notify();
