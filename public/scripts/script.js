const cart = new Vue({
    delimiters: ['${', '}'],
    el: "#cart",
    data: function () {
        return {
            list: [],
            amount : 0,
            error : false,
            response : ""
        };
    },
    methods : {
        async betConfirmed(){

            console.log(this.amount);

            if (this.amount <= 0){
                this.error = true;
                this.response = "You can't bet 0 €, aren't you a bit stupid ?";
                return false;
            }

            let res = await axios.post('http://127.0.0.1:8000/checkBet', {
                'bets' : this.list,
                'amount' : this.amount
            });

            console.log(res.data);
            console.log(`Status code: ${res.status}`);

            if (res.data.result === 'success'){
                this.error = false;
                setCredits(res.data.credits)
                this.list = [];
            }
            else {
                this.error = true
            }

            this.response = res.data.message;
        },
        deleteChoice(choice){
            let index = this.list.indexOf(choice);
            this.list.splice(index, 1);
        }
    },
    computed : {
        getTotalOdd(){
            let totalOdd = 1;
            this.list.forEach(item => {
                totalOdd *= item.odd;
            })
            return totalOdd.toFixed(2);
        },
        getPotentialGain(){
            let odd = this.getTotalOdd;
            let am = this.amount;
            let mul = odd * am;
            return mul.toFixed(2);
        }
    }
});

const betList = new Vue({
    delimiters: ['${', '}'],
    el: "#betList",
    data: function () {
        return {
            list: []
        };
    },
    methods: {
        betClicked(id, homeTeam, awayTeam, date, league, odd, choice){
            for (let item of cart.list){
                if (id === item.id){
                    return  alert("You can't combine two odds on the same match !")
                }
            }

            const bet = {
                "id" : id,
                "homeTeam" : homeTeam,
                "awayTeam" : awayTeam,
                "date" : date,
                "league" : league,
                "odd" : odd,
                "choice" : choice
            }
            cart.list.push(bet)
        }
    }
});

/*const test = new Vue({
    delimiters: ['${', '}'],
    el: "#test",
    data: function () {
        return {
            'col-12' : true,
            'col-9' : false
        };
    },
    methods : {
        getClass(){
            if (cart.list.length > 0) {
                return {
                    'col-12': false,
                    'col-9': true
                }
            }
            else {
                return {
                    'col-12': true,
                    'col-9': false
                }
            }
        }
    }
});*/

function setCredits(credits){
    document.getElementById('credits').innerHTML = credits
}