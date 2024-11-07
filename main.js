async function main(memberNR) {
	const eventData = await fetchData(
		"http://localhost:3000/index.php?type=json&n=207"
	);
	console.log(eventData);

	const ical = ics();
	eventData.forEach((eventObject) => {
		const event = eventObject.properties;
		ical.addEvent(
			event.title,
			event.description,
			event.place,
			event.begin,
			event.end
		);
	});
	ical.download("spejderKalender");
}
async function fetchData(url) {
	try {
		const response = await fetch(url);
		if (!response.ok) {
			throw new Error(`Response status: ${response.status}`);
		}

		const json = await response.json();
		//console.log(json);

		return json;
	} catch (error) {
		console.error(error.message);
	}
}
