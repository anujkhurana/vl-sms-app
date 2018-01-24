import { Injectable } from '@angular/core';
import { AppService } from './app.service';
import { globals } from '../app.global';

@Injectable()
export class ApiService {

  public dateFormat: string = globals.dateFormat;
  public apiUrl: string = globals.apiUrl;

  constructor(private app: AppService) { }

  private _getToken(){
    return {'Authorization': `Bearer ${globals.apitoken}`};
  }

  public getTrips(body: any){
    return this.app.request(`${this.apiUrl}/trips`,'post',body,this._getToken());
  }

  public getDepartures( body: any){
    return this.app.request(`${this.apiUrl}/departures`,'post',body, this._getToken());
  }

  public getParticipants(id: string){
    return this.app.request(`${this.apiUrl}/trip-passengers`,'post',{departure_id: id}, this._getToken());
  }

  public sendSMStoClients(body: any){
    return this.app.request(`${this.apiUrl}/send-sms`,'post', body, this._getToken());
  }

  public getSMSnotifications(body: any){
    return this.app.request(`${this.apiUrl}/sms-notifications`,'post', body, this._getToken());
  }

}
