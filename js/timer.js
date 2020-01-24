class Timer{
    constructor(callback, delay){
        this.callback = callback;
        this.timerId, this.start, this.remaining = delay;
    }

    startTimer(){
        this.resume();
    }

    pause() {
        window.clearTimeout(this.timerId);
        this.remaining -= new Date() - this.start;
    };

    resume() {
        this.start = new Date();
        window.clearTimeout(this.timerId);
        this.timerId = window.setTimeout(this.callback, this.remaining);
    };

    reset() {
        clearTimeout(this);
        this.startTimer();
    }
}
