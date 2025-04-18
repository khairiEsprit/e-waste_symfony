import os
import base64
import json
import logging
import numpy as np
import cv2
from flask import Flask, request, jsonify
from flask_cors import CORS
from deepface import DeepFace
from deepface.commons import functions

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

# Configuration
FACE_DETECTION_MODEL = "opencv"  # Options: opencv, ssd, dlib, mtcnn, retinaface
FACE_RECOGNITION_MODEL = "VGG-Face"  # Options: VGG-Face, Facenet, Facenet512, OpenFace, DeepFace, ArcFace
DISTANCE_METRIC = "cosine"  # Options: cosine, euclidean, euclidean_l2
CONFIDENCE_THRESHOLD = 0.6  # Lower is more strict (for cosine similarity)
UPLOADS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "uploads")

# Create uploads directory if it doesn't exist
os.makedirs(UPLOADS_DIR, exist_ok=True)

def base64_to_image(base64_string):
    """Convert base64 string to OpenCV image"""
    if "base64," in base64_string:
        base64_string = base64_string.split("base64,")[1]
    
    img_data = base64.b64decode(base64_string)
    nparr = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    return img

def detect_faces(img):
    """Detect faces in an image using OpenCV"""
    try:
        # Convert to grayscale for face detection
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Use OpenCV's face detector
        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        faces = face_cascade.detectMultiScale(gray, 1.1, 4)
        
        if len(faces) == 0:
            return {"success": False, "message": "No face detected in the image"}
        
        if len(faces) > 1:
            return {"success": False, "message": "Multiple faces detected in the image"}
        
        return {"success": True, "faces": faces.tolist()}
    except Exception as e:
        logger.error(f"Error in face detection: {str(e)}")
        return {"success": False, "message": f"Error in face detection: {str(e)}"}

def extract_embeddings(img):
    """Extract face embeddings using DeepFace"""
    try:
        # Detect faces first to ensure there's exactly one face
        detection_result = detect_faces(img)
        if not detection_result["success"]:
            return detection_result
        
        # Extract embeddings using DeepFace
        embedding_obj = DeepFace.represent(img, model_name=FACE_RECOGNITION_MODEL, detector_backend=FACE_DETECTION_MODEL)
        
        # DeepFace.represent returns a list of dictionaries, each with an 'embedding' key
        if not embedding_obj or not isinstance(embedding_obj, list) or len(embedding_obj) == 0:
            return {"success": False, "message": "Failed to extract face embeddings"}
        
        # Get the first embedding (we've already verified there's only one face)
        embedding = embedding_obj[0]["embedding"]
        
        return {
            "success": True, 
            "embedding": embedding,
            "model": FACE_RECOGNITION_MODEL
        }
    except Exception as e:
        logger.error(f"Error in embedding extraction: {str(e)}")
        return {"success": False, "message": f"Error in embedding extraction: {str(e)}"}

def compare_embeddings(embedding1, embedding2):
    """Compare two face embeddings and return similarity score"""
    try:
        # Convert to numpy arrays if they're not already
        if isinstance(embedding1, list):
            embedding1 = np.array(embedding1)
        if isinstance(embedding2, list):
            embedding2 = np.array(embedding2)
        
        # Calculate distance based on the selected metric
        if DISTANCE_METRIC == "cosine":
            distance = functions.findCosineDistance(embedding1, embedding2)
            # Convert distance to similarity (1 - distance for cosine)
            similarity = 1 - distance
        elif DISTANCE_METRIC == "euclidean":
            distance = functions.findEuclideanDistance(embedding1, embedding2)
            # For euclidean, lower is better, so we need to convert to a similarity score
            # This is a simple conversion, might need adjustment
            max_distance = 2.0  # Maximum possible euclidean distance for normalized vectors
            similarity = 1 - (distance / max_distance)
        else:
            distance = functions.findEuclideanDistance(embedding1, embedding2)
            similarity = 1 - (distance / 2.0)
        
        # Check if similarity exceeds threshold
        is_match = similarity >= CONFIDENCE_THRESHOLD
        
        return {
            "success": True,
            "is_match": is_match,
            "similarity": float(similarity),
            "threshold": CONFIDENCE_THRESHOLD
        }
    except Exception as e:
        logger.error(f"Error in embedding comparison: {str(e)}")
        return {"success": False, "message": f"Error in embedding comparison: {str(e)}"}

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({"status": "healthy"})

@app.route('/detect-face', methods=['POST'])
def detect_face_endpoint():
    """Endpoint to detect if an image contains a single face"""
    try:
        data = request.json
        if not data or 'image' not in data:
            return jsonify({"success": False, "message": "No image provided"}), 400
        
        # Convert base64 to image
        img = base64_to_image(data['image'])
        if img is None:
            return jsonify({"success": False, "message": "Invalid image data"}), 400
        
        # Detect faces
        result = detect_faces(img)
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in detect-face endpoint: {str(e)}")
        return jsonify({"success": False, "message": str(e)}), 500

@app.route('/extract-embedding', methods=['POST'])
def extract_embedding_endpoint():
    """Endpoint to extract face embeddings from an image"""
    try:
        data = request.json
        if not data or 'image' not in data:
            return jsonify({"success": False, "message": "No image provided"}), 400
        
        # Convert base64 to image
        img = base64_to_image(data['image'])
        if img is None:
            return jsonify({"success": False, "message": "Invalid image data"}), 400
        
        # Extract embeddings
        result = extract_embeddings(img)
        
        # Convert numpy array to list for JSON serialization
        if result["success"] and "embedding" in result:
            result["embedding"] = result["embedding"].tolist()
        
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in extract-embedding endpoint: {str(e)}")
        return jsonify({"success": False, "message": str(e)}), 500

@app.route('/compare-embeddings', methods=['POST'])
def compare_embeddings_endpoint():
    """Endpoint to compare two face embeddings"""
    try:
        data = request.json
        if not data or 'embedding1' not in data or 'embedding2' not in data:
            return jsonify({"success": False, "message": "Both embeddings are required"}), 400
        
        # Compare embeddings
        result = compare_embeddings(data['embedding1'], data['embedding2'])
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in compare-embeddings endpoint: {str(e)}")
        return jsonify({"success": False, "message": str(e)}), 500

@app.route('/verify-face', methods=['POST'])
def verify_face_endpoint():
    """Endpoint to verify a face against stored embeddings"""
    try:
        data = request.json
        if not data or 'image' not in data or 'stored_embedding' not in data:
            return jsonify({"success": False, "message": "Image and stored embeddings are required"}), 400
        
        # Convert base64 to image
        img = base64_to_image(data['image'])
        if img is None:
            return jsonify({"success": False, "message": "Invalid image data"}), 400
        
        # Extract embeddings from the uploaded image
        extraction_result = extract_embeddings(img)
        if not extraction_result["success"]:
            return jsonify(extraction_result)
        
        # Compare with stored embeddings
        comparison_result = compare_embeddings(
            extraction_result["embedding"], 
            data['stored_embedding']
        )
        
        return jsonify(comparison_result)
    except Exception as e:
        logger.error(f"Error in verify-face endpoint: {str(e)}")
        return jsonify({"success": False, "message": str(e)}), 500

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=True)
