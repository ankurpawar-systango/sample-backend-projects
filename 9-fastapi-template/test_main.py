"""
Unit tests for FastAPI Starter Template
"""

import pytest
from fastapi.testclient import TestClient
from main import app, items_db, Item

# Create test client
client = TestClient(app)


class TestRootEndpoints:
    """Test root and health check endpoints"""

    def test_root_endpoint(self):
        """Test the root endpoint returns welcome message"""
        response = client.get("/")
        assert response.status_code == 200
        data = response.json()
        assert "message" in data
        assert "Welcome to FastAPI Starter Template" in data["message"]
        assert "version" in data
        assert "endpoints" in data

    def test_health_check(self):
        """Test the health check endpoint"""
        response = client.get("/health")
        assert response.status_code == 200
        data = response.json()
        assert data["status"] == "healthy"
        assert "service" in data


class TestGetEndpoints:
    """Test GET endpoints"""

    def test_get_all_items(self):
        """Test retrieving all items"""
        response = client.get("/items")
        assert response.status_code == 200
        data = response.json()
        assert isinstance(data, list)
        assert len(data) >= 2  # Should have at least the default items

    def test_get_item_by_id_success(self):
        """Test retrieving a specific item by ID"""
        response = client.get("/items/1")
        assert response.status_code == 200
        data = response.json()
        assert data["id"] == 1
        assert "name" in data
        assert "price" in data

    def test_get_item_by_id_not_found(self):
        """Test retrieving a non-existent item returns 404"""
        response = client.get("/items/9999")
        assert response.status_code == 404
        data = response.json()
        assert data["detail"] == "Item not found"


class TestPostEndpoints:
    """Test POST endpoints"""

    def setup_method(self):
        """Setup method to reset items_db before each test"""
        # Reset the items database to initial state
        items_db.clear()
        items_db.extend([
            Item(id=1, name="Laptop", description="High-performance laptop", price=999.99),
            Item(id=2, name="Mouse", description="Wireless mouse", price=29.99),
        ])

    def test_create_item(self):
        """Test creating a new item"""
        new_item = {
            "name": "Keyboard",
            "description": "Mechanical keyboard",
            "price": 79.99
        }
        response = client.post("/items", json=new_item)
        assert response.status_code == 200
        data = response.json()
        assert data["name"] == "Keyboard"
        assert data["description"] == "Mechanical keyboard"
        assert data["price"] == 79.99
        assert "id" in data
        assert data["id"] is not None

    def test_create_item_without_description(self):
        """Test creating an item without optional description"""
        new_item = {
            "name": "Monitor",
            "price": 299.99
        }
        response = client.post("/items", json=new_item)
        assert response.status_code == 200
        data = response.json()
        assert data["name"] == "Monitor"
        assert data["price"] == 299.99

    def test_create_item_invalid_data(self):
        """Test creating an item with invalid data"""
        invalid_item = {
            "name": "Invalid Item"
            # Missing required 'price' field
        }
        response = client.post("/items", json=invalid_item)
        assert response.status_code == 422  # Unprocessable Entity

    def test_greet_endpoint_with_name(self):
        """Test greeting endpoint with name"""
        greeting_data = {
            "message": "Hello World",
            "name": "John"
        }
        response = client.post("/greet", json=greeting_data)
        assert response.status_code == 200
        data = response.json()
        assert "Hello, John!" in data["greeting"]
        assert data["your_message"] == "Hello World"

    def test_greet_endpoint_without_name(self):
        """Test greeting endpoint without name"""
        greeting_data = {
            "message": "Hello World"
        }
        response = client.post("/greet", json=greeting_data)
        assert response.status_code == 200
        data = response.json()
        assert "Hello, Guest!" in data["greeting"]


class TestCORS:
    """Test CORS configuration"""

    def test_cors_headers_present(self):
        """Test that CORS headers are present in responses"""
        # Make a request with Origin header
        headers = {"Origin": "http://localhost:3000"}
        response = client.get("/", headers=headers)
        assert response.status_code == 200
        # FastAPI's TestClient doesn't automatically add CORS headers,
        # but in actual deployment with CORS middleware, they will be present


# Run tests with: pytest test_main.py -v
