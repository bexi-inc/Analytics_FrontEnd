class visibilityChange {

    constructor(){
        this.prefix = visibilityChange.getBrowserPrefix()
        this.hidden = visibilityChange.hiddenProperty(this.prefix)
        this.visibilityState = visibilityChange.visibilityState(this.prefix)
        this.visibilityEvent = visibilityChange.visibilityEvent(this.prefix)
    }

    get _visibilityEvent(){
        return this.visibilityEvent
    }

    get _hidden(){
        return this.hidden
    }

    static getBrowserPrefix() {     
        if ('hidden' in document) {
            return null
        }
        
        var browserPrefixes = ['moz', 'ms', 'o', 'webkit']

        for (var i = 0; i < browserPrefixes.length; i++) {
            var prefix = browserPrefixes[i] + 'Hidden'
            if (prefix in document) {
                return browserPrefixes[i]
            }
        }

        return null
    }

    
    static hiddenProperty(prefix) {
        if (prefix) {
            return prefix + 'Hidden'
        } else {
            return 'hidden'
        }
    }

    
    static visibilityState(prefix) {
        if (prefix) {
            return prefix + 'VisibilityState'
        } else {
            return 'visibilityState'
        }
    }

    
    static visibilityEvent(prefix) {
        if (prefix) {
            return prefix + 'visibilitychange'
        } else {
            return 'visibilitychange'
        }
    }
}