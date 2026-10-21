# Grade Insights Service (ML.NET & ASP.NET Core)

This microservice provides automated machine learning grade prediction using ML.NET and ASP.NET Core Web API.

## Requirements
- .NET 8.0 SDK (or later)

## How to Run
1. Open terminal in this folder:
   ```bash
   cd GradeInsightsService
   dotnet restore
   dotnet run
   ```
   Or double-click `run.bat`.

2. The service will start listening at:
   - **Base URL**: `http://localhost:5000`
   - **Predict Endpoint**: `POST http://localhost:5000/api/insights/predict`
   - **Swagger Docs**: `http://localhost:5000/swagger`
   - **Health Check**: `GET http://localhost:5000/api/insights/health`

## API Schema

### Request (POST `/api/insights/predict`)
```json
{
  "studyHours": 12.0,
  "attendancePercentage": 92.5,
  "previousGrade": 84.0
}
```

### Response
```json
{
  "predictedScore": 87.8
}
```
