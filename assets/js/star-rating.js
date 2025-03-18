function generateStarRating(container, rating) {
  // Clear previous stars
  container.innerHTML = "";

  // Ensure rating is between 0 and 5
  rating = Math.min(5, Math.max(0, parseFloat(rating) || 0));

  const fullStars = Math.floor(rating);
  const decimalPart = rating % 1;
  const emptyStars = 5 - Math.ceil(rating);

  // Add full stars
  for (let i = 0; i < fullStars; i++) {
    const star = document.createElement("div");
    star.classList.add("star", "filled");
    container.appendChild(star);
  }

  // Add partial star if needed
  if (decimalPart > 0) {
    const partialStar = document.createElement("div");
    partialStar.classList.add("star", "partial");
    partialStar.style.setProperty("--fill-percent", `${decimalPart * 100}%`);
    container.appendChild(partialStar);
  }

  // Add empty stars
  for (let i = 0; i < emptyStars; i++) {
    const star = document.createElement("div");
    star.classList.add("star");
    container.appendChild(star);
  }

  // Set ARIA label for accessibility
  container.setAttribute(
    "aria-label",
    `Rating: ${rating.toFixed(1)} out of 5 stars`
  );
}

// Initialize all star ratings on the page
document.addEventListener("DOMContentLoaded", function () {
  const ratingContainers = document.querySelectorAll(
    ".top10-brokers-star-rating"
  );
  ratingContainers.forEach((container) => {
    const rating = parseFloat(container.dataset.rating) || 0;
    generateStarRating(container, rating);
  });
});
