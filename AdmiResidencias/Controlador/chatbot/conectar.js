const chatBody = document.querySelector(".chat-body");
const messageInput = document.querySelector(".msg-input");
const sendMessageButton = document.querySelector("#send-msg");
const chatbotToggler = document.querySelector("#chat-tog");
const closeChatbot = document.querySelector("#close-chat");

const API_KEY = `AIzaSyBDJIPpNPFE1Fmehrk9nrEvFJzRkdpdoYY`;
const API_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${API_KEY}`;

const userData = {
	msg: null
}

const chatHistory = [];
const initialInpHei = messageInput.scrollHeight;

const createMessageElement = (content, ...classes) => {
	const div = document.createElement("div");
	div.classList.add("msg", ...classes);
	div.innerHTML = content;
	return div;
}

const generateBotResponse = async (incomingMessageDiv) => {
	const messageElement = incomingMessageDiv.querySelector(".msg-txt");
	
	chatHistory.push({
		role: "user",
		parts: [{ text: userData.msg }]
	});

	

	const requestOptions = {
		method: "POST",
		headers: {"Content-Type": "application/json"},
		body: JSON.stringify({
			system_instruction: {
			    parts:[{text: "Eres un asistente virtual de la pagina de residencias del Tecnológico de Estudios Superiores de Cuautitlán Izcalli (TESCI). Tu trabajo es guiar a los usuarios a través de las distintas páginas y resolver sus dudas.\nSe breve y conciso.\nEvite inventar información. Si no es posible solucionar una duda, recomiende contactarnos y proporcione el número de contacto.\nEvite salir del tema de residencias profesionales del TESCI o de la pagina que las administra."},
			    {text: "input: ¿Qué es una residencia?"},
			    {text: "output: Es la actividad realizada durante el desarrollo de un proyecto, en cualquiera de las áreas de desarrollo establecidas, que defina una problemática y proponga una solución viable."},
			    {text: "input: ¿Qué debo cubrir para darme de alta?"},
			    {text: "output: • Acreditación del Servicio Social.\n• Acreditación de actividades complementarias.\n• Tener aprobado al menos el 80% de créditos de su plan de estudios.\n• No contar con ninguna asignatura en condiciones de \"curso especial\"."},
			    {text: "input: ¿Cuánto tiempo debe durar una residencia profesional?"},
			    {text: "output: Debe durar un período de 4 meses como tiempo mínimo y 6 meses como tiempo máximo, deben acumularse un mínimo de 500 horas."},
			    {text: "input: ¿Cómo me registro?"},
			    {text: "output: Puede dirigirse al Coordinador de Carrera."},
			    {text: "input: Quiero hablar con un administrador."},
			    {text: "output: Puede contactarnos por medio del siguiente número: \n(55) 58 6431 7071"},
			    {text: "input: ¿Como se hace un proyecto?"},
			    {text: "output: El proyecto de Residencia Profesional puede realizarse de manera individual, grupal o interdisciplinaria; dependiendo de los requerimientos, condiciones y características del proyecto de la empresa, organismo o dependencia."},
			    {text: "input: ¿Para qué es el botón de acceder?"},
			    {text: "output: Al presionarlo, te permite ingresar tu correo electrónico y contraseña para acceder a la pagina con la que podrás ver los detalles de tu proyecto.\n\nSi no estás registrado en la plataforma, también puedes hacerlo desde el mismo menú."},
			    {text: "input: Quiero hablar con el profesor que supervisa mi proyecto."},
			    {text: "output: Los detalles de contacto del profesor a cargo de tu proyecto están en tu página de alumno."},
			    {text: "input: ¿Cómo va mi proyecto?"},
			    {text: "output: Los detalles de tu proyecto de residencias están en tu página de alumno."},
			    {text: "input: ¿Cómo puedo contactarlos?"},
			    {text: "output: En la página de Contactos puedes encontrar un medio para enviarnos un mensaje. Ahí también encontraras un mapa que te lleve al TESCI.\n\nTambién puedes llamarnos a este número:\n(55) 58 6431 7071"},
			    {text: "input: ¿Cuáles son sus métricas?"},
			    {text: "output: Puedes encontrar nuestras métricas residenciales en la pagina de Proyecciones, a la que puedes acceder por medio del encabezado."},
			    {text: "input: ¿Cómo van los proyectos de mis alumnos?"},
			    {text: "output: Los detalles de los proyectos de residencias que tienen los alumnos bajo tu supervisión están en tu página de profesor."},
			    {text: "input: ¿Qué tipo de residencias hay?"},
			    {text: "output: El proyecto de Residencia Profesional puede realizarse de manera individual, grupal o interdisciplinaria; dependiendo de los requerimientos, condiciones y características del proyecto de la empresa, organismo o dependencia."}]},
			contents: chatHistory
		})
	}

	try {
		const response = await fetch(API_URL, requestOptions);
		const data = await response.json();
		if (!response.ok) throw new Error(data.error.msg);

		const apiResTxt = data.candidates[0].content.parts[0].text.replace(/\*\*(.*?)\*\*/g, "$1").trim();
		messageElement.innerText = apiResTxt;

		chatHistory.push({
			role: "model",
			parts: [{ text: apiResTxt }]
		});
	} catch (error) {
		console.log(error);
		messageElement.innerText = error.msg;
		messageElement.style.color = "#ff0000";
	} finally {
		incomingMessageDiv.classList.remove("think");
		chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
	}
}

const handleOutgoingMessage = (e) => {
	e.preventDefault();
	userData.msg = messageInput.value.trim();
	messageInput.value = "";
	messageInput.dispatchEvent(new Event("input"));

	const messageContent = `<div class="msg-txt"></div>`;

	const outgoingMessageDiv = createMessageElement(messageContent, "usr-msg");
	outgoingMessageDiv.querySelector(".msg-txt").innerText = userData.msg;
	chatBody.appendChild(outgoingMessageDiv);
	chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });

	setTimeout(() => {
		const messageContent = `<svg class="bot-avt" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
				    <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
				<div class="msg-txt">
					<div class="think-ind">
						<div class="dot"></div>
						<div class="dot"></div>
						<div class="dot"></div>
					</div>
				</div>`;

		const incomingMessageDiv = createMessageElement(messageContent, "bot-msg", "think");
		chatBody.appendChild(incomingMessageDiv);
		chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: "smooth" });
		generateBotResponse(incomingMessageDiv);
	}, 600);
}

messageInput.addEventListener("keydown", (e) => {
	const userMessage = e.target.value.trim();
	if (e.key === "Enter" && userMessage && !e.shiftKey) {
		handleOutgoingMessage(e);
	}
});

messageInput.addEventListener("input", () => {
	messageInput.style.heigth = `${initialInpHei}px`;
	messageInput.style.heigth = `${messageInput.scrollHeight}px`;
	document.querySelector(".chat-form").style.borderRadius = messageInput.scrollHeight > initialInpHei ? "15px" : "32px";
});

sendMessageButton.addEventListener("click", (e) => handleOutgoingMessage(e));
chatbotToggler.addEventListener("click", () => document.body.classList.toggle("show-chat"));
closeChatbot.addEventListener("click", () => document.body.classList.remove("show-chat"));

