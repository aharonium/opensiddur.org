function customSocialShareMastodon(el) {
    const title = decodeURIComponent(el.dataset.title);
    const url   = decodeURIComponent(el.dataset.url);
    const text  = `${title} ${url}`;

    let instance = localStorage.getItem('mastodon_instance');

    if (!instance) {
        instance = prompt(
            "Enter your Mastodon instance (e.g. mastodon.social):",
            "mastodon.social"
        );

        if (!instance) return false;

        instance = instance
            .replace(/^https?:\/\//, '')
            .replace(/\/$/, '');

        localStorage.setItem('mastodon_instance', instance);
    }

    const shareUrl =
        `https://${instance}/share?text=${encodeURIComponent(text)}`;

    // Navigate instead of opening a popup
    window.location.href = shareUrl;

    return false;
}
