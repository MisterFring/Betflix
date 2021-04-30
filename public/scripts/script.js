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
                document.getElementById('alertBet').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('alertBet').style.display = 'none';
                }, 2000);               
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
            document.getElementById('alertBet').style.display = 'block';
            setTimeout(function() {
                document.getElementById('alertBet').style.display = 'none';
            }, 2000);            
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
        betClicked(id, homeTeam, awayTeam, date, league, odd, choice, homeLogo, awayLogo){
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
                "choice" : choice,
                "homeLogo": homeLogo,
                "awayLogo": awayLogo
            }
            cart.list.push(bet)
        }
    }
});

const countList = new Vue({
    delimiters: ['${', '}'],
    el: "#countList",
    computed: {
        getListLength() {
            return cart.list.length
        }
    } 
});

function setCredits(credits){
    document.getElementById('credits').innerHTML = Math.round(credits * 100) / 100
}


function toggleDarkLight() {
    var $bet_card = $(".darkmo");
    var $cart = $(".chat-popup");
    var $cart_background= $(".cart_background");
    $bet_card.toggleClass("dark-mode light-mode")
    $cart.toggleClass("dark-mode light-mode")
    $cart_background.toggleClass("dark-mode light-mode")
}
