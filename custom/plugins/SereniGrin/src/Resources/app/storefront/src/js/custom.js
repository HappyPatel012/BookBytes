document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.tab-btn');
    const tabs = document.querySelectorAll('.tab-content');

    tabs.forEach((tab, index) => {
        if(index !== 0) tab.style.display = 'none';
    });

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            console.log("clicked")
            const target = btn.getAttribute('data-tab');

            tabs.forEach(tab => tab.style.display = 'none');
            document.querySelector('.' + target).style.display = 'block';

            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
});