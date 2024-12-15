/*
document.addEventListener('contextmenu', function(e) {
  e.preventDefault();
});*/

function onload() {
	$('#mainMenuContainer').show();
	$('#strawberry1, #strawberry2, #strawberry3').show();
	$('#mainGameContainer').hide();
	resetGame();

	$('#loadingScreen').hide();
}

function toggleLoadingScreen(show) {
	const $loadingScreen = $('#loadingScreen');
	if (show) {
		$loadingScreen.addClass('fade-in').show();
		setTimeout(() => $loadingScreen.removeClass('fade-in'), 500);
	} else {
		$loadingScreen.addClass('fade-out');
		setTimeout(() => $loadingScreen.removeClass('fade-out').hide(), 500);
	}
}

function openWindow(windowID, status) {
	if (status) {
		getRealWord((realWord) => $('#correctWord').text(realWord)); // Only if status is not null
	}

	$('#results').text(status === 'success' ? 'Congratulations!' : status === 'failure' ? "That's too bad!" : '');

	const $window = $('#' + windowID)
		.addClass('scale-in-center')
		.show();
	setTimeout(() => $window.removeClass('scale-in-center'), 500);
}

function closeWindow(windowID) {
	const $window = $('#' + windowID).addClass('slide-out-blurred-bottom');
	setTimeout(() => $window.removeClass('slide-out-blurred-bottom').hide(), 500);
}

function playGame() {
	toggleLoadingScreen(true);

	setTimeout(() => {
		generateWord();
		$('#mainMenuContainer').hide();
		$('#strawberry1, #strawberry2, #strawberry3').hide();
		$('#mainGameContainer').show();
	}, 500);

	setTimeout(() => {
		toggleLoadingScreen(false);
	}, 800);
}

function resetGame() {
	currentRow = 0;
	currentColumn = 0;
	letterStatus = {};

	// Clear text and reset styles for game cells and keyboard
	$('.row').children().text('').css('background-color', '');
	$('.keyboard-button').css({ 'background-color': '', color: '', border: '0' });
}

function playAgain() {
	toggleLoadingScreen(true);
	closeWindow('endingWindowContainer');
	generateWord();
	resetGame();

	setTimeout(() => {
		toggleLoadingScreen(false);
		$('#mainGameContainer').addClass('fade-in').show();
	}, 500);

	setTimeout(() => $('#mainGameContainer').removeClass('fade-in'), 1200);
}

function mainMenu() {
	toggleLoadingScreen(true);
	setTimeout(() => {
		closeWindow('endingWindowContainer');
		$('#mainGameContainer').hide();
		resetGame();
		$('#mainMenuContainer').show();
		$('#strawberry1, #strawberry2, #strawberry3').show();
	}, 500);

	setTimeout(() => toggleLoadingScreen(false), 1200);
}

function displayError(message) {
	const $errorMessage = $('#errorMessage').text(message).addClass('slide-in-top').show();
	setTimeout(() => $errorMessage.removeClass('slide-in-top'), 500);

	setTimeout(() => $errorMessage.addClass('slide-out-top'), 3000);
	setTimeout(() => $errorMessage.removeClass('slide-out-top').hide(), 3500);
}

//BOARD FUNCTIONS
let currentRow = 0,
	currentColumn = 0,
	letterStatus = {};
const keyPressHandler = (e) => {
	const key = e.key.toUpperCase();
	if (/^[A-Z]$/.test(key)) keyPress(key);
	else if (key === 'ENTER' || key === 'ENTER') keyPress('Enter');
	else if (key === 'BACKSPACE') keyPress('Backspace');
};

$(document).on('keydown', keyPressHandler);

function keyPress(key) {
	const $cell = $('.row').eq(currentRow).children().eq(currentColumn);

	if (key === 'Enter') {
		if (currentColumn === 5) {
			let word = $('.row')
				.eq(currentRow)
				.children()
				.toArray()
				.map((cell) => $(cell).text())
				.join('');
			checkComplete(word);
		} else {
			displayError('Not a five letter word.');
		}
	} else if (key === 'Backspace' && currentColumn > 0) {
		currentColumn--;
		$('.row').eq(currentRow).children().eq(currentColumn).text('');
	} else if (/^[a-zA-Z]$/.test(key) && currentColumn < 5) {
		$cell.text(key.toUpperCase());
		currentColumn++;
	}
}

let isProcessing = false;

function checkComplete(word) {
	if (isProcessing) return;
	isProcessing = true;
	checkRealWord(word);
}

function checkRealWord(word) {
	$.get('utilities/checkRealWord.php', { word })
		.done((response) => {
			try {
				const result = JSON.parse(response);
				result.is_real ? checkLetters(word) : displayError('Not a real word.');
			} catch {
				displayError('There was an error processing the response.');
			}
		})
		.fail(() => displayError('There was an error checking the word.'))
		.always(() => {
			isProcessing = false;
		});
}

function checkLetters(word) {
	$.get('utilities/checkLetters.php', { word })
		.done((response) => {
			try {
				const result = JSON.parse(response.trim().startsWith('{') ? response : '');
				if (!result) throw new Error('Invalid JSON response');

				const feedback = result.feedback;
				feedback.forEach((status, i) => {
					const letter = word[i];
					const $cell = $('.row').eq(currentRow).children().eq(i);
					letterStatus[letter] = letterStatus[letter] || { correct: 0, inWord: 0, notInWord: 0 };

					if (status === 'correct') {
						letterStatus[letter].correct++;
						$cell.css('background-color', 'var(--green)');
					} else if (status === 'inWord') {
						letterStatus[letter].inWord++;
						$cell.css('background-color', 'var(--yellow)');
					} else {
						letterStatus[letter].notInWord++;
						$cell.css('background-color', 'var(--light-pink)');
					}

					updateKeyboardButton(letter);
				});

				currentRow++;
				currentColumn = 0;

				if (feedback.every((status) => status === 'correct')) {
					openWindow('endingWindowContainer', 'success');
				} else if (currentRow >= 6) {
					openWindow('endingWindowContainer', 'failure');
				}
			} catch {
				displayError('There was an error processing the response.');
			}
		})
		.fail(() => displayError('There was an error checking your guess.'))
		.always(() => {
			isProcessing = false;
		});
}

async function generateWord() {
	try {
		await fetch('utilities/generateWord.php');
	} catch {
		toggleLoadingScreen(true);
		displayError('Failed to fetch a random word.');
		return null;
	}
}

function updateKeyboardButton(letter) {
	if (['Enter', 'Backspace'].includes(letter)) return;

	const status = letterStatus[letter] || {};
	const color = status.correct ? 'var(--green)' : status.inWord ? 'var(--yellow)' : 'var(--light-pink)';
	const $button = $(`button:contains('${letter}')`).not('button:contains("Enter")').not('button:contains("Delete")');

	if ($button.length) {
		$button.css({ 'background-color': color, border: '2px solid var(--dark-primary)' });
	}
}

function getRealWord(callback) {
	$.get('utilities/getRealWord.php', callback);
}
