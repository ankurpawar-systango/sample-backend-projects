# FastAPI Starter Template

A simple FastAPI application template with sample endpoints demonstrating GET and POST requests, CORS configuration, and proper project structure.

## Features

- FastAPI application with sample endpoints
- CORS middleware configured for cross-origin requests
- RESTful API examples (GET and POST)
- Pydantic models for request/response validation
- In-memory data storage for demonstration
- Health check endpoint
- Auto-generated API documentation (Swagger UI)

## Requirements

- Python 3.8 or higher
- pip (Python package installer)

## Setup Instructions

### 1. Create a Virtual Environment (Recommended)

```bash
# Create virtual environment
python -m venv venv

# Activate virtual environment
# On Windows:
venv\Scripts\activate
# On macOS/Linux:
source venv/bin/activate
```

### 2. Install Dependencies

```bash
pip install -r requirements.txt
```

## Running the Application

### Method 1: Using Python directly

```bash
python main.py
```

### Method 2: Using Uvicorn command

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

The application will start on `http://localhost:8000`

## API Endpoints

### GET Endpoints

- **`GET /`** - Welcome message with API overview
- **`GET /health`** - Health check endpoint
- **`GET /items`** - Retrieve all items
- **`GET /items/{item_id}`** - Retrieve a specific item by ID

### POST Endpoints

- **`POST /items`** - Create a new item
  ```json
  {
    "name": "string",
    "description": "string (optional)",
    "price": 0.0
  }
  ```

- **`POST /greet`** - Send a personalized greeting
  ```json
  {
    "message": "string",
    "name": "string (optional)"
  }
  ```

## API Documentation

Once the application is running, you can access:

- **Swagger UI**: `http://localhost:8000/docs`
- **ReDoc**: `http://localhost:8000/redoc`

## Testing the API

### Using cURL

```bash
# Get all items
curl http://localhost:8000/items

# Get specific item
curl http://localhost:8000/items/1

# Create a new item
curl -X POST http://localhost:8000/items \
  -H "Content-Type: application/json" \
  -d '{"name":"Keyboard","description":"Mechanical keyboard","price":79.99}'

# Send a greeting
curl -X POST http://localhost:8000/greet \
  -H "Content-Type: application/json" \
  -d '{"message":"Hello from cURL","name":"John"}'
```

### Using the Frontend

The frontend template in the `Sample-Frontend-Project` repository demonstrates how to make API calls to this backend using JavaScript fetch API.

## CORS Configuration

The application is configured to allow cross-origin requests from any origin (`allow_origins=["*"]`). 

**Important**: In production, you should restrict this to specific origins:

```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://your-frontend-domain.com"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

## Project Structure

```
9-fastapi-template/
├── main.py              # Main FastAPI application
├── requirements.txt     # Python dependencies
├── README.md           # This file
└── test_main.py        # Unit tests
```

## Development

To run in development mode with auto-reload:

```bash
uvicorn main:app --reload
```

## Next Steps

- Add database integration (PostgreSQL, MongoDB, etc.)
- Implement authentication and authorization
- Add more endpoints for your specific use case
- Deploy to production (Docker, Heroku, AWS, etc.)
- Add environment-based configuration
- Implement logging and monitoring

## Resources

- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [Pydantic Documentation](https://docs.pydantic.dev/)
- [Uvicorn Documentation](https://www.uvicorn.org/)
