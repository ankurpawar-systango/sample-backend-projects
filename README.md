
## 🖥 Technologies Used
![HTML](https://img.shields.io/badge/HTML-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-%23563D7C.svg?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-%23F7DF1C.svg?style=for-the-badge&logo=javascript&logoColor=black)
![jQuery](https://img.shields.io/badge/jQuery-%230e76a8.svg?style=for-the-badge&logo=jquery&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)

## 🏥 Health Check

The backend includes health check endpoints to monitor service status and availability.

### Endpoint Overview

**GET /health** — Returns HTTP 200 status code when the service is operational.

### Response Format

Both the PHP and FastAPI implementations return health status information:

```json
{
  "status": "healthy",
  "message": "Backend service is running",
  "timestamp": "2024-01-15 10:30:45",
  "responseTime": "1.25 ms"
}
```

### PHP Implementation

**Location:** `7-health/health.php`

The PHP health check endpoint returns comprehensive health status information including PHP version and uptime details.

**Example curl command:**
```bash
curl http://localhost/sample-backend-projects/7-health/health.php
```

**Response example:**
```json
{
  "status": "healthy",
  "message": "Backend service is running",
  "timestamp": "2024-01-15 10:30:45",
  "responseTime": "1.25 ms",
  "php_version": "7.4.3",
  "uptime": "Service is operational"
}
```

For more details, see [`7-health/README.md`](7-health/README.md).

### FastAPI Implementation

**Location:** `9-fastapi-template/main.py` (lines 63-66)

The FastAPI health check endpoint is a lightweight endpoint that returns health status.

**Example curl command:**
```bash
curl http://localhost:8000/health
```

**Response example:**
```json
{
  "status": "healthy",
  "service": "FastAPI Starter Template"
}
```

For more details and other endpoints, see [`9-fastapi-template/README.md`](9-fastapi-template/README.md).

### Server Unavailability & Error Handling

When the server is down or unavailable, attempting to access the `/health` endpoint will result in an **HTTP 500 error**. This indicates that the service is not operational and cannot process the health check request.

**Expected behavior by server state:**

- **Server is healthy and running:** `GET /health` returns **HTTP 200** with status information
- **Server is down or unavailable:** `GET /health` returns **HTTP 500** (Internal Server Error)

### Testing the Health Endpoints

You can verify the health status using any HTTP client:

```bash
# Test the PHP health endpoint
curl -i http://localhost/sample-backend-projects/7-health/health.php

# Test the FastAPI health endpoint (assuming it runs on port 8000)
curl -i http://localhost:8000/health
```

Both endpoints return HTTP 200 (OK) when the service is healthy and operational. If the server is down or unavailable, the request will fail with an HTTP 500 error, indicating the service is not accessible.

## 📜 License
This project is open-source and available under the **MIT License**.

## 🤝 Contributing  
🎯 Contributions are welcome! If you have suggestions or want to enhance the project, feel free to fork the repository and submit a pull request.

## 📬 Connect with Me  
💬 I love meeting new people and discussing tech, business, and creative ideas. Let’s connect! You can reach me on these platforms:

<div align="center">
  <table>
    <tr>
      <td>
        <a href="https://iqbolshoh.uz" target="_blank">
          <img src="https://img.icons8.com/color/48/domain.png" 
               height="40" width="40" alt="Website" title="Website" />
        </a>
      </td>
      <td>
        <a href="mailto:iilhomjonov777@gmail.com" target="_blank">
          <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/gmail.svg"
               height="40" width="40" alt="Email" title="Email" />
        </a>
      </td>
      <td>
        <a href="https://github.com/iqbolshoh" target="_blank">
          <img src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/github.svg"
               height="40" width="40" alt="GitHub" title="GitHub" />
        </a>
      </td>
      <td>
        <a href="https://www.linkedin.com/in/iqbolshoh/" target="_blank">
          <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/linkedin.svg"
               height="40" width="40" alt="LinkedIn" title="LinkedIn" />
        </a>
      </td>
      <td>
        <a href="https://t.me/iqbolshoh_777" target="_blank">
          <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/telegram.svg"
               height="40" width="40" alt="Telegram" title="Telegram" />
        </a>
      </td>
      <td>
        <a href="https://wa.me/998997799333" target="_blank">
          <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/whatsapp.svg"
               height="40" width="40" alt="WhatsApp" title="WhatsApp" />
        </a>
      </td>
      <td>
        <a href="https://instagram.com/iqbolshoh_777" target="_blank">
          <img src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/instagram.svg"
               height="40" width="40" alt="Instagram" title="Instagram" />
        </a>
      </td>
      <td>
        <a href="https://x.com/iqbolshoh_777" target="_blank">
          <img src="https://img.shields.io/badge/X-000000?style=for-the-badge&logo=x&logoColor=white"
               height="40" width="40" alt="X" title="X (Twitter)" />
        </a>
      </td>
      <td>
        <a href="https://www.youtube.com/@Iqbolshoh_777" target="_blank">
          <img src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/youtube.svg"
               height="40" width="40" alt="YouTube" title="YouTube" />
        </a>
      </td>
    </tr>
  </table>
</div>
