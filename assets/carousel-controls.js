console.info('[carousel-controls] script loaded');

function moveCarousel(button) {
    const track = button.closest('.team-section, .resound-section, .software-section')
        ?.querySelector('.team-track.scrolling, .resound-track.scrolling, .software-track.is-scrolling');
    const animations = track?.getAnimations() ?? [];
    const animation = animations.find((item) => item.playState !== 'idle');
    const direction = Number(button.dataset.carouselSpeed);

    console.info('[carousel-controls] button clicked', {
        direction,
        section: button.closest('section')?.id,
        trackFound: Boolean(track),
        animationsFound: animations.length,
        animationFound: Boolean(animation),
        playState: animation?.playState,
        playbackRate: animation?.playbackRate,
    });

    if (!track || !animation) {
        console.warn('[carousel-controls] cannot accelerate: track or animation missing');
        return;
    }

    const currentOffset = new DOMMatrixReadOnly(getComputedStyle(track).transform).m41;
    const targetOffset = currentOffset - direction * 400;
    const cycleDuration = Number(animation.effect.getTiming().duration);
    const cycleWidth = track.scrollWidth / 2;
    const from = currentOffset;
    const to = targetOffset;

    animation.pause();
    track.classList.add('is-manually-moving');
    console.info('[carousel-controls] moving 400px', { from, to });

    const move = track.animate(
        [
            { transform: `translate3d(${from}px, 0, 0)` },
            { transform: `translate3d(${to}px, 0, 0)` },
        ],
        { duration: 380, easing: 'cubic-bezier(.2,.75,.25,1)' },
    );

    move.onfinish = () => {
        track.classList.remove('is-manually-moving');
        animation.currentTime = (animation.currentTime ?? 0)
            + direction * cycleDuration * (400 / cycleWidth);
        move.cancel();
        animation.play();
    };
}

document.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-carousel-speed]') : null;
    if (button) moveCarousel(button);
});
