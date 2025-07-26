<!-- <style>
.animated-col {
    opacity: 0;
    transform: translateX(30px);
    animation: fadeInUp 1s forwards;
}
.animated-col:nth-child(2) {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeIn 3s forwards;
}
.animated-col:nth-child(3) {
    opacity: 0;
    transform: translateX(30px);
    animation: fadeInUp 5s forwards;
}
// .animated-col:nth-child(2) { animation-delay: 0.3s; }
// .animated-col:nth-child(3) { animation-delay: 0.5s; }
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: none;
    }
} -->
</style>
<div class="row mb-3 p-3">
<h3 class="text-center">About Us</h3>
<hr>
    <div class="col-md-4 animated-col" style="animation: flyInLeft 1s forwards;">
        <div class="card card-body bg-primary text-center text-light">DPeace App</div>
        <p class="p-3">DPeace App is a Nigerian digital platform offering seamless and affordable solutions for airtime
        top-ups, data purchases, and bill payments across major providers like SWIFT, Spectranet,
        Smile, Glo, MTN, Airtel, 9mobile, DStv, GOtv, Startimes, and electricity tokens, among others.
        Users benefit from instant transactions and earn bonuses ranging from 0.3% to 10% on select
        services, making it a cost-effective choice for everyday digital needs. With a user-friendly
        interface and reliable service, DPeace App is the go-to platform for Nigerians seeking
        convenience and to save more and pay less in daily transactions.</p>
    </div>

    <style>
    @keyframes flyInLeft {
        from {
            opacity: 0;
            transform: translateX(-100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    </style>

    <div class="col-md-4 animated-col" style="animation: flyInBottom 1s forwards;">
        <div class="card card-body bg-primary text-center text-light">Vision Statement</div>
        <p class="p-3">DPeace App: Creating impact, improving lives. Together, we empower every Nigerian, at home or abroad, to thrive. 
        Your efforts and mine unite to bring good into existence.</p>
    </div>

    <style>
    @keyframes flyInBottom {
        from {
            opacity: 0;
            transform: translateY(100%);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>

    <div class="col-md-4 animated-col" style="animation: flyInRight 1s forwards;">
        <div class="card card-body bg-primary text-center text-light">Mission Statement</div>
        <p class="p-3">At DPeace App, our mission is to empower Nigerians by providing discounted access to vital services,
        making everyday life more affordable and dignified. We are driven by passion, sincerity, and
        transparency, striving to support the economy by enabling individuals and families to save more and pay
        less. Through collaborative efforts, we aim to foster a better life for all, we believe “Better life
        begins with you”.</p>
    </div>

    <style>
    @keyframes flyInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    </style>

</div>
