document.addEventListener('DOMContentLoaded', () => {

	document.addEventListener('click', async (event) => {

		const button = event.target.closest(
			'#opensiddur-debug-log-refresh'
		);

		if ( ! button ) {
			return;
		}

		const container = document.getElementById(
			'opensiddur-debug-log-container'
		);

		if ( ! container ) {
			return;
		}

		button.disabled = true;

		const original = button.textContent;

		button.textContent = 'Refreshing…';

		const data = new FormData();

		data.append(
			'action',
			'opensiddur_debug_log_refresh'
		);

		data.append(
			'nonce',
			OpenSiddurDebugLog.nonce
		);

		try {

			const response = await fetch(
				OpenSiddurDebugLog.ajaxurl,
				{
					method: 'POST',
					credentials: 'same-origin',
					body: data
				}
			);

			const result = await response.json();

			if ( result.success ) {

                container.innerHTML = result.data.html;
                return;

			} else {

				alert(
					result.data.message ??
					'Unable to refresh log.'
				);

				button.disabled = false;
				button.textContent = original;
			}

		} catch ( error ) {

			alert(
				'Unable to contact the server.'
			);

			button.disabled = false;
			button.textContent = original;

		}

	});

});