import { NgModule }             from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// import for page components
import { LoginComponent } from './pages/login/login.component';
import { DefaultComponent  } from './pages/default/default.component';
import { NotfoundComponent } from './pages/notfound/notfound.component';
import { SmsNotificationComponent } from './pages/sms-notification/sms-notification.component';

// import for miscellaneous reusable components
import { MainDropdownsComponent } from './components/main-dropdowns/main-dropdowns.component';
import { SendSmsComponent } from './components/send-sms/send-sms.component';

import { AuthGuard } from './guards/auth-guard.service';

const routes: Routes = [
    { path: '', redirectTo: '/send-sms', pathMatch: 'full' },
    { path: 'login', component: LoginComponent, data: {title: "VL SMS App - Login"}, canActivate: [AuthGuard] },
    { path: 'send-sms', component: DefaultComponent, data: {title: "VL SMS App - Send SMS"}, canActivate: [AuthGuard]},
    { path: 'send-sms/:trip_slug', component: DefaultComponent, data: {title: "VL SMS App - Send SMS slug"}, canActivate: [AuthGuard]},
    { path: 'sms-notifications', component: SmsNotificationComponent, data: {title: "VL SMS App - Notifications"}, canActivate: [AuthGuard]},
    { path: 'sms-notifications/:trip_slug', component: SmsNotificationComponent, data: {title: "VL SMS App - Notifications slug"}, canActivate: [AuthGuard]},
    { path: '**', component: NotfoundComponent}
];

@NgModule({
  imports: [ RouterModule.forRoot(routes) ],
  exports: [ RouterModule ]
})
export class AppRoutingModule {}

export const routedComponents = [ LoginComponent, DefaultComponent, SmsNotificationComponent, NotfoundComponent ];

export const miscComponents = [ MainDropdownsComponent, SendSmsComponent ]

export const prettyUrlRoutes = [ 'send-sms', 'sms-notifications' ];
