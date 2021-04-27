const majority = 18 * 365 * 24 * 60 * 60  * 1000

const signupApp = new Vue({
    el: '#signup-app',
    delimiters: ['${','}'],

    data: function() {
		return {
            theDate: '',
		}
    },
    
    computed: {
        isMajeur() {

            if (this.theDate == '') {
                return ''
            }
            const myDate = Date.now()
            const input = new Date(this.theDate)
            const diff = myDate - input
            console.log(diff, majority)

            if (diff <= majority) {
                return "Must be 18 yorld"
            }
        }
    }
})