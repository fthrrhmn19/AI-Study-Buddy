(function () {
    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function initCursorGlow() {
        const hero = document.querySelector('.premium-hero');
        if (!hero || reducedMotion) {
            return;
        }

        hero.addEventListener('pointermove', function (event) {
            const rect = hero.getBoundingClientRect();
            const x = ((event.clientX - rect.left) / rect.width) * 100;
            const y = ((event.clientY - rect.top) / rect.height) * 100;
            hero.style.setProperty('--glow-x', x + '%');
            hero.style.setProperty('--glow-y', y + '%');
        }, { passive: true });
    }

    function initParticleNetwork() {
        const canvas = document.getElementById('landing-particles-canvas');
        if (!canvas || reducedMotion) {
            return;
        }

        const ctx = canvas.getContext('2d');
        let width = 0;
        let height = 0;
        let particles = [];

        function resize() {
            const rect = canvas.getBoundingClientRect();
            width = canvas.width = Math.max(1, Math.floor(rect.width * window.devicePixelRatio));
            height = canvas.height = Math.max(1, Math.floor(rect.height * window.devicePixelRatio));
            ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);

            const count = Math.min(76, Math.max(36, Math.floor(rect.width / 18)));
            particles = Array.from({ length: count }, function () {
                return {
                    x: Math.random() * rect.width,
                    y: Math.random() * rect.height,
                    vx: (Math.random() - 0.5) * 0.22,
                    vy: (Math.random() - 0.5) * 0.22,
                    r: 1 + Math.random() * 1.6
                };
            });
        }

        function draw() {
            const rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);

            particles.forEach(function (particle, index) {
                particle.x += particle.vx;
                particle.y += particle.vy;

                if (particle.x < 0 || particle.x > rect.width) {
                    particle.vx *= -1;
                }

                if (particle.y < 0 || particle.y > rect.height) {
                    particle.vy *= -1;
                }

                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(191, 219, 254, .5)';
                ctx.fill();

                for (let j = index + 1; j < particles.length; j += 1) {
                    const next = particles[j];
                    const dx = particle.x - next.x;
                    const dy = particle.y - next.y;
                    const distance = Math.hypot(dx, dy);

                    if (distance < 128) {
                        ctx.strokeStyle = 'rgba(96, 165, 250, ' + ((1 - distance / 128) * 0.2) + ')';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particle.x, particle.y);
                        ctx.lineTo(next.x, next.y);
                        ctx.stroke();
                    }
                }
            });

            requestAnimationFrame(draw);
        }

        resize();
        window.addEventListener('resize', resize, { passive: true });
        draw();
    }

    initCursorGlow();
    initParticleNetwork();
})();
