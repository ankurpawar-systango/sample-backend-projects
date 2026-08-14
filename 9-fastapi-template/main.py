"""
FastAPI Starter Template
A simple FastAPI application with sample endpoints demonstrating GET and POST requests.
"""

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional
import uvicorn

# Initialize FastAPI app
app = FastAPI(
    title="FastAPI Starter Template",
    description="A starter template for FastAPI applications",
    version="1.0.0"
)

# Configure CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, replace with specific origins
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic models for request/response
class Item(BaseModel):
    id: Optional[int] = None
    name: str
    description: Optional[str] = None
    price: float

class Message(BaseModel):
    message: str
    name: Optional[str] = None

# In-memory storage (for demonstration purposes)
items_db: List[Item] = [
    Item(id=1, name="Laptop", description="High-performance laptop", price=999.99),
    Item(id=2, name="Mouse", description="Wireless mouse", price=29.99),
]

# Root endpoint
@app.get("/")
async def root():
    """Welcome endpoint"""
    return {
        "message": "Welcome to FastAPI Starter Template",
        "version": "1.0.0",
        "endpoints": {
            "GET /": "This welcome message",
            "GET /health": "Health check endpoint",
            "GET /items": "Get all items",
            "GET /items/{item_id}": "Get specific item by ID",
            "POST /items": "Create a new item",
            "POST /greet": "Send a greeting message"
        }
    }

# Health check endpoint
@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "healthy", "service": "FastAPI Starter Template"}

# GET endpoint - Retrieve all items
@app.get("/items", response_model=List[Item])
async def get_items():
    """Retrieve all items from the database"""
    return items_db

# GET endpoint - Retrieve specific item by ID
@app.get("/items/{item_id}", response_model=Item)
async def get_item(item_id: int):
    """Retrieve a specific item by ID"""
    for item in items_db:
        if item.id == item_id:
            return item
    raise HTTPException(status_code=404, detail="Item not found")

# POST endpoint - Create a new item
@app.post("/items", response_model=Item)
async def create_item(item: Item):
    """Create a new item"""
    # Generate new ID
    if items_db:
        new_id = max(item.id for item in items_db if item.id) + 1
    else:
        new_id = 1

    item.id = new_id
    items_db.append(item)
    return item

# POST endpoint - Send a greeting
@app.post("/greet")
async def greet(message: Message):
    """Send a personalized greeting"""
    name = message.name if message.name else "Guest"
    return {
        "greeting": f"Hello, {name}!",
        "your_message": message.message,
        "response": f"Thanks for your message: '{message.message}'"
    }

# Run the application
if __name__ == "__main__":
    uvicorn.run("main:app", host="0.0.0.0", port=8000, reload=True)
